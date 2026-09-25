<?php

namespace App\Filament\Student\Pages;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Pages\Page;

class PaymentReceipt extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'payments/{payment}/receipt';

    protected string $view = 'filament.student.pages.payment-receipt';

    public Payment $payment;

    public function mount(Payment $payment): void
    {
        abort_unless($payment->user_id === auth()->id(), 404);
        abort_unless($payment->status === PaymentStatus::Successful, 404);

        $this->payment = $payment->load(['course', 'user']);
    }

    public function getHeading(): string
    {
        return 'Payment receipt';
    }
}
