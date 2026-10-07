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
        $payment->refresh();

        if ($payment->status === PaymentStatus::Successful) {
            $this->syncPaymentCourseAccess->handle($payment);

            return true;
        }

        $verification = $this->gateway->verify($payment->reference);

        if (! $verification->successful) {
            $payment->update(['provider_response' => $verification->response]);

            return false;
        }

        return $this->handleVerified($payment, $verification);
    }

    public function handleVerified(Payment $payment, PaymentVerification $verification): bool
    {
        $payment->refresh();

        if ($payment->status === PaymentStatus::Successful) {
            $this->syncPaymentCourseAccess->handle($payment);

            return true;
        }

        if (! $verification->successful) {
            return false;
        }

        $expectedAmount = (int) round((float) $payment->amount * 100);
        $amountMatches = $verification->amount === $expectedAmount
            || ($verification->requestedAmount === $expectedAmount
                && $verification->amount >= $expectedAmount);

        if ($verification->reference !== $payment->reference
            || ! $amountMatches
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
