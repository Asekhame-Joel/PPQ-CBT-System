<?php

namespace App\Payments;

use App\Enums\AccessSource;
use App\Enums\PaymentStatus;
use App\Models\CourseAccess;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class SyncPaymentCourseAccess
{
    public function handle(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->getKey());
            $access = CourseAccess::query()
                ->where('user_id', $payment->user_id)
                ->where('course_id', $payment->course_id)
                ->lockForUpdate()
                ->first();

            if ($payment->status === PaymentStatus::Successful) {
                if ($access?->access_source === AccessSource::Admin) {
                    return;
                }

                CourseAccess::query()->updateOrCreate(
                    [
                        'user_id' => $payment->user_id,
                        'course_id' => $payment->course_id,
                    ],
                    [
                        'payment_id' => $payment->getKey(),
                        'access_source' => AccessSource::Payment,
                        'granted_at' => now(),
                        'expires_at' => null,
                        'is_active' => true,
                    ],
                );

                return;
            }

            if ($access?->access_source !== AccessSource::Payment
                || $access->payment_id !== $payment->getKey()) {
                return;
            }

            $replacementPayment = Payment::query()
                ->where('user_id', $payment->user_id)
                ->where('course_id', $payment->course_id)
                ->where('status', PaymentStatus::Successful)
                ->whereKeyNot($payment->getKey())
                ->latest('paid_at')
                ->first();

            if ($replacementPayment) {
                $access->update([
                    'payment_id' => $replacementPayment->getKey(),
                    'granted_at' => $replacementPayment->paid_at ?? now(),
                    'expires_at' => null,
                    'is_active' => true,
                ]);

                return;
            }

            $access->update(['is_active' => false]);
        });
    }
}
