<?php

namespace Tests\Feature\Payments;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use App\Payments\PaystackGateway;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaystackGatewayTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_initialization_sends_the_exact_payment_to_paystack_in_minor_units(): void
    {
        config()->set('services.paystack.secret_key', 'test-secret');
        $student = User::factory()->create(['email' => 'student@example.com']);
        $course = Course::factory()->create();
        $payment = Payment::factory()->for($student)->for($course)->create([
            'reference' => 'EXAM-PAYSTACK-001',
            'amount' => '1500.00',
            'currency' => 'NGN',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/access-code'],
            ]),
        ]);

        $result = app(PaystackGateway::class)->initialize($payment->load('user'), 'https://example.test/return');

        $this->assertSame('https://checkout.paystack.com/access-code', $result->authorizationUrl);
        Http::assertSent(fn (Request $request): bool => $request->hasHeader('Authorization', 'Bearer test-secret')
            && $request['email'] === 'student@example.com'
            && $request['amount'] === 150000
            && $request['currency'] === 'NGN'
            && $request['reference'] === 'EXAM-PAYSTACK-001'
            && $request['callback_url'] === 'https://example.test/return'
            && $request['metadata']['payment_id'] === $payment->id);
    }

    public function test_verification_maps_the_provider_response(): void
    {
        config()->set('services.paystack.secret_key', 'test-secret');
        Http::preventStrayRequests();
        Http::fake([
            'https://api.paystack.co/transaction/verify/EXAM-PAYSTACK-002' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => 'EXAM-PAYSTACK-002',
                    'amount' => 250000,
                    'currency' => 'NGN',
                ],
            ]),
        ]);

        $result = app(PaystackGateway::class)->verify('EXAM-PAYSTACK-002');

        $this->assertTrue($result->successful);
        $this->assertSame('EXAM-PAYSTACK-002', $result->reference);
        $this->assertSame(250000, $result->amount);
        $this->assertSame('NGN', $result->currency);
    }
}
