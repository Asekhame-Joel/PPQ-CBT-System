<?php

namespace App\Payments;

class PaymentVerification
{
    /** @param array<string, mixed> $response */
    public function __construct(
        public readonly bool $successful,
        public readonly string $reference,
        public readonly int $amount,
        public readonly string $currency,
        public readonly array $response,
    ) {}
}
