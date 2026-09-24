<?php

namespace App\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    public function initialize(Payment $payment, string $callbackUrl): PaymentInitialization;

    public function verify(string $reference): PaymentVerification;
}
