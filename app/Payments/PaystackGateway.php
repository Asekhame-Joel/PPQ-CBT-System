<?php

namespace App\Payments;

use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackGateway implements PaymentGateway
{
    public function initialize(Payment $payment, string $callbackUrl): PaymentInitialization
    {
        $response = $this->client()
            ->post('/transaction/initialize', [
                'email' => $payment->user->email,
                'amount' => $this->toMinorUnits($payment->amount),
                'currency' => $payment->currency,
                'reference' => $payment->reference,
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'payment_id' => $payment->getKey(),
                    'course_id' => $payment->course_id,
                    'user_id' => $payment->user_id,
                ],
            ])
            ->throw()
            ->json();

        $authorizationUrl = data_get($response, 'data.authorization_url');

        if (! is_string($authorizationUrl) || ! $this->isPaystackCheckoutUrl($authorizationUrl)) {
            throw new RuntimeException('Paystack did not return a valid authorization URL.');
        }

        return new PaymentInitialization($authorizationUrl, $response);
    }

    public function verify(string $reference): PaymentVerification
    {
        $response = $this->client()
            ->retry(2, 200)
            ->get('/transaction/verify/'.urlencode($reference))
            ->throw()
            ->json();

        return new PaymentVerification(
            successful: data_get($response, 'data.status') === 'success',
            reference: (string) data_get($response, 'data.reference', ''),
            amount: (int) data_get($response, 'data.amount', 0),
            currency: (string) data_get($response, 'data.currency', ''),
            response: $response,
        );
    }

    private function client(): PendingRequest
    {
        $secretKey = config('services.paystack.secret_key');

        if (! is_string($secretKey) || blank($secretKey)) {
            throw new RuntimeException('Paystack is not configured.');
        }

        return Http::baseUrl((string) config('services.paystack.base_url'))
            ->withToken($secretKey)
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }

    private function toMinorUnits(string $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function isPaystackCheckoutUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        if ($scheme !== 'https' || ! is_string($host)) {
            return false;
        }

        $host = strtolower($host);

        return $host === 'paystack.com' || str_ends_with($host, '.paystack.com');
    }
}
