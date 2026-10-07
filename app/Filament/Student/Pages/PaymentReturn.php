<?php

namespace App\Filament\Student\Pages;

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

    public function mount(Payment $payment, ConfirmCoursePayment $confirmPayment): void
    {
        abort_unless($payment->user_id === auth()->id(), 404);

        $this->payment = $payment->load('course');

        $this->confirmPayment($confirmPayment);
    }

    public function retryVerification(ConfirmCoursePayment $confirmPayment): void
    {
        $this->confirmPayment($confirmPayment);
    }

    private function confirmPayment(ConfirmCoursePayment $confirmPayment): void
    {
        try {
            $this->successful = $confirmPayment->handle($this->payment);
            $this->payment->refresh();

            if ($this->successful) {
                $this->redirect(PracticeSetup::getUrl(['course' => $this->payment->course_id], panel: 'student'));

                return;
            }

            Log::warning('Paystack payment has not completed verification yet.', [
                'payment_id' => $this->payment->id,
                'reference' => $this->payment->reference,
                'provider_status' => data_get($this->payment->provider_response, 'data.status'),
                'provider_message' => data_get($this->payment->provider_response, 'message'),
            ]);
        } catch (Throwable $exception) {
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
