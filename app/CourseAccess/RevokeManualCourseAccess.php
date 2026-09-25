<?php

namespace App\CourseAccess;

use App\Enums\AccessSource;
use App\Models\CourseAccess;
use Illuminate\Validation\ValidationException;

class RevokeManualCourseAccess
{
    public function handle(CourseAccess $access): void
    {
        if ($access->access_source !== AccessSource::Admin) {
            throw ValidationException::withMessages([
                'access' => 'Only manually granted access can be revoked here.',
            ]);
        }

        $access->update(['is_active' => false]);
    }
}
