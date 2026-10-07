<?php

namespace App\Filament\Student\Pages;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Payments\ConfirmCoursePayment;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentReturn extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'payments/{payment}/return';

    protected string $view = 'filament.student.pages.payment-return';

    public Payment $payment;

    public bool $successful = false;

    public bool $verificationPaused = false;

    public int $verificationAttempts = 0;

    public ?string $verificationNotice = null;

    public function mount(Payment $payment, ConfirmCoursePayment $confirmPayment): void
    {
        abort_unless($payment->user_id === auth()->id(), 404);

        $this->payment = $payment->load('course');
        $this->confirmPayment($confirmPayment);
    }

    public function retryVerification(ConfirmCoursePayment $confirmPayment): void
    {
        if ($this->successful || $this->verificationPaused) {
            return;
        }

        $this->verificationAttempts++;
        $this->confirmPayment($confirmPayment);

        if (! $this->successful && $this->verificationAttempts >= 12) {
            $this->verificationPaused = true;
            $this->verificationNotice = 'Paystack has not confirmed this transaction yet. You can safely check again without making another payment.';
        }
    }

    public function checkAgain(ConfirmCoursePayment $confirmPayment): void
    {
        $this->verificationPaused = false;
        $this->verificationAttempts = 0;
        $this->verificationNotice = null;

        $this->confirmPayment($confirmPayment);
    }

    private function confirmPayment(ConfirmCoursePayment $confirmPayment): void
    {
        try {
            $this->payment->refresh()->load('course');

            if ($this->payment->status === PaymentStatus::Successful) {
                $this->successful = true;

                return;
            }

            $this->successful = $confirmPayment->handle($this->payment);
            $this->payment->refresh();

            if ($this->successful) {
                $this->verificationNotice = null;

                return;
            }

            $providerStatus = data_get($this->payment->provider_response, 'data.status');
            $this->verificationNotice = is_string($providerStatus) && filled($providerStatus)
                ? 'Paystack currently reports this payment as '.str_replace('_', ' ', $providerStatus).'.'
                : null;

            Log::warning('Paystack payment has not completed verification yet.', [
                'payment_id' => $this->payment->id,
                'reference' => $this->payment->reference,
                'provider_status' => data_get($this->payment->provider_response, 'data.status'),
                'provider_message' => data_get($this->payment->provider_response, 'message'),
            ]);
        } catch (Throwable $exception) {
            $this->verificationNotice = 'We could not reach Paystack to confirm this payment. We will keep checking automatically.';

            report($exception);

            Log::error('Paystack payment verification failed on the return page.', [
                'payment_id' => $this->payment->id,
                'reference' => $this->payment->reference,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function getHeading(): string
    {
        return 'Payment status';
    }
}
