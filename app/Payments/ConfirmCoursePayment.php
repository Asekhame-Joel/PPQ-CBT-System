<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConfirmCoursePayment
{
    public function __construct(
        private PaymentGateway $gateway,
        private SyncPaymentCourseAccess $syncPaymentCourseAccess,
    ) {}

    public function handle(Payment $payment): bool
    {
        if ($payment->status === PaymentStatus::Successful) {
            return true;
        }

        $verification = $this->gateway->verify($payment->reference);
        $expectedAmount = (int) round((float) $payment->amount * 100);

        if (! $verification->successful) {
            $payment->update(['provider_response' => $verification->response]);

            return false;
        }

        if ($verification->reference !== $payment->reference
            || $verification->amount !== $expectedAmount
            || $verification->currency !== $payment->currency) {
            $payment->update(['provider_response' => $verification->response]);

            throw new RuntimeException('The verified payment details do not match this transaction.');
        }

        DB::transaction(function () use ($payment, $verification): void {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->getKey());

            if ($lockedPayment->status !== PaymentStatus::Successful) {
                $lockedPayment->update([
                    'status' => PaymentStatus::Successful,
                    'paid_at' => now(),
                    'provider_response' => $verification->response,
                ]);
            }

            $this->syncPaymentCourseAccess->handle($lockedPayment);
        });

        return true;
    }
}
