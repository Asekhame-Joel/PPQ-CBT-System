<?php

namespace App\CourseAccess;

use App\Enums\AccessSource;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GrantCourseAccess
{
    public function handle(User $student, Course $course, ?CarbonInterface $expiresAt = null): CourseAccess
    {
        if ($student->role !== UserRole::Student
            || ! Course::query()->active()->eligibleFor($student)->whereKey($course)->exists()) {
            throw ValidationException::withMessages([
                'course_id' => 'Select an active course available to this student.',
            ]);
        }

        if ($expiresAt?->isPast()) {
            throw ValidationException::withMessages([
                'expires_at' => 'The expiry date must be in the future.',
            ]);
        }

        return DB::transaction(function () use ($student, $course, $expiresAt): CourseAccess {
            $access = CourseAccess::query()
                ->whereBelongsTo($student)
                ->whereBelongsTo($course)
                ->lockForUpdate()
                ->first();

            if ($access && $access->access_source !== AccessSource::Admin) {
                throw ValidationException::withMessages([
                    'course_id' => 'Access granted by a payment or promotion cannot be replaced manually.',
                ]);
            }

            $access ??= new CourseAccess([
                'user_id' => $student->getKey(),
                'course_id' => $course->getKey(),
            ]);

            $access->fill([
                'payment_id' => null,
                'access_source' => AccessSource::Admin,
                'granted_at' => now(),
                'expires_at' => $expiresAt,
                'is_active' => true,
            ])->save();

            return $access;
        });
    }
}
