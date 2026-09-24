<?php

namespace App\Filament\Student\Pages;

use App\Models\Payment;
use App\Payments\ConfirmCoursePayment;
use Filament\Pages\Page;
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

        try {
            $this->successful = $confirmPayment->handle($payment);
            $this->payment->refresh();
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function getHeading(): string
    {
        return 'Payment status';
    }
}
