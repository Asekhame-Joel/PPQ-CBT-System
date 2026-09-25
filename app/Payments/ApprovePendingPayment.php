<?php

namespace App\Payments;

use App\Enums\AccessSource;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\CourseAccess;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovePendingPayment
{
    public function handle(Payment $payment, User $admin): Payment
    {
        if ($admin->role !== UserRole::Admin) {
            throw ValidationException::withMessages([
                'payment' => 'Only an administrator can approve a pending payment.',
            ]);
        }

        return DB::transaction(function () use ($payment, $admin): Payment {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->getKey());

            if ($lockedPayment->status !== PaymentStatus::Pending) {
                throw ValidationException::withMessages([
                    'payment' => 'Only pending payments can be approved manually.',
                ]);
            }

            $providerResponse = $lockedPayment->provider_response ?? [];
            $providerResponse['manual_approval'] = [
                'admin_id' => $admin->getKey(),
                'approved_at' => now()->toIso8601String(),
            ];

            $lockedPayment->update([
                'status' => PaymentStatus::Successful,
                'paid_at' => now(),
                'provider_response' => $providerResponse,
            ]);

            CourseAccess::query()->updateOrCreate(
                [
                    'user_id' => $lockedPayment->user_id,
                    'course_id' => $lockedPayment->course_id,
                ],
                [
                    'payment_id' => $lockedPayment->getKey(),
                    'access_source' => AccessSource::Payment,
                    'granted_at' => now(),
                    'expires_at' => null,
                    'is_active' => true,
                ],
            );

            return $lockedPayment->refresh();
        });
    }
}
