<?php

namespace App\Payments;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Filament\Student\Pages\PaymentReturn;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class InitiateCoursePayment
{
    public function __construct(private PaymentGateway $gateway) {}

    public function handle(User $student, Course $course): PaymentInitialization
    {
        $eligible = Course::query()
            ->active()
            ->eligibleFor($student)
            ->whereKey($course)
            ->exists();

        if (! $eligible || $course->questions()->active()->count() < $course->min_question_count) {
            throw ValidationException::withMessages([
                'course' => 'This course is not currently available for purchase.',
            ]);
        }

        if (CourseAccess::query()->available()->whereBelongsTo($student)->whereBelongsTo($course)->exists()) {
            throw ValidationException::withMessages([
                'course' => 'You already have access to this course.',
            ]);
        }

        $payment = Payment::query()->create([
            'user_id' => $student->getKey(),
            'course_id' => $course->getKey(),
            'reference' => 'EXAM-'.Str::upper((string) Str::ulid()),
            'amount' => $course->price,
            'currency' => 'NGN',
            'status' => PaymentStatus::Pending,
            'provider' => PaymentProvider::Paystack,
        ]);

        try {
            $initialization = $this->gateway->initialize(
                $payment->load('user'),
                PaymentReturn::getUrl(['payment' => $payment], panel: 'student'),
            );

            $payment->update(['provider_response' => $initialization->response]);

            return $initialization;
        } catch (Throwable $exception) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'provider_response' => ['message' => $exception->getMessage()],
            ]);

            throw $exception;
        }
    }
}
