<?php

namespace App\Payments;

class PaymentInitialization
{
    /** @param array<string, mixed> $response */
    public function __construct(
        public readonly string $authorizationUrl,
        public readonly array $response,
    ) {}
}
