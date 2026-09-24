<?php

namespace Tests\Feature\Payments;

use App\Enums\PaymentStatus;
use App\Jobs\ProcessPaystackWebhook;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Payment;
use App\Models\User;
use App\Payments\ConfirmCoursePayment;
use App\Payments\PaymentGateway;
use App\Payments\PaymentVerification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class PaystackWebhookTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_success_webhook_is_acknowledged_and_queued(): void
    {
        config()->set('services.paystack.secret_key', 'webhook-secret');
        Queue::fake([ProcessPaystackWebhook::class]);
        $payload = ['event' => 'charge.success', 'data' => ['reference' => 'EXAM-WEBHOOK-001']];

        $response = $this->withHeader('x-paystack-signature', $this->signature($payload))
            ->postJson(route('webhooks.paystack'), $payload);

        $response->assertOk();
        Queue::assertPushed(
            ProcessPaystackWebhook::class,
            fn (ProcessPaystackWebhook $job): bool => $job->reference === 'EXAM-WEBHOOK-001',
        );
    }

    public function test_invalid_signature_is_rejected_without_queuing_work(): void
    {
        config()->set('services.paystack.secret_key', 'webhook-secret');
        Queue::fake([ProcessPaystackWebhook::class]);
        $payload = ['event' => 'charge.success', 'data' => ['reference' => 'EXAM-WEBHOOK-002']];

        $response = $this->withHeader('x-paystack-signature', 'invalid-signature')
            ->postJson(route('webhooks.paystack'), $payload);

        $response->assertUnauthorized();
        Queue::assertNotPushed(ProcessPaystackWebhook::class);
    }

    public function test_unrelated_event_is_acknowledged_without_queuing_work(): void
    {
        config()->set('services.paystack.secret_key', 'webhook-secret');
        Queue::fake([ProcessPaystackWebhook::class]);
        $payload = ['event' => 'subscription.create', 'data' => ['reference' => 'EXAM-WEBHOOK-003']];

        $response = $this->withHeader('x-paystack-signature', $this->signature($payload))
            ->postJson(route('webhooks.paystack'), $payload);

        $response->assertOk();
        Queue::assertNotPushed(ProcessPaystackWebhook::class);
    }

    public function test_webhook_job_reverifies_payment_and_grants_access(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();
        $payment = Payment::factory()->for($student)->for($course)->create([
            'reference' => 'EXAM-WEBHOOK-004',
            'amount' => '2000.00',
            'currency' => 'NGN',
        ]);
        $this->mock(PaymentGateway::class, function (MockInterface $mock): void {
            $mock->shouldReceive('verify')->once()->with('EXAM-WEBHOOK-004')
                ->andReturn(new PaymentVerification(true, 'EXAM-WEBHOOK-004', 200000, 'NGN', [
                    'data' => ['status' => 'success'],
                ]));
        });

        (new ProcessPaystackWebhook('EXAM-WEBHOOK-004'))->handle(app(ConfirmCoursePayment::class));

        $this->assertSame(PaymentStatus::Successful, $payment->fresh()->status);
        $access = CourseAccess::query()->sole();
        $this->assertSame($student->id, $access->user_id);
        $this->assertSame($course->id, $access->course_id);
        $this->assertSame($payment->id, $access->payment_id);
    }

    public function test_repeated_success_processing_does_not_duplicate_access(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();
        $payment = Payment::factory()->successful()->for($student)->for($course)->create([
            'reference' => 'EXAM-WEBHOOK-005',
        ]);
        CourseAccess::factory()->for($student)->for($course)->for($payment)->create();
        $this->mock(PaymentGateway::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('verify');
        });

        (new ProcessPaystackWebhook('EXAM-WEBHOOK-005'))->handle(app(ConfirmCoursePayment::class));

        $this->assertDatabaseCount('course_access', 1);
        $this->assertSame(PaymentStatus::Successful, $payment->fresh()->status);
    }

    /** @param array<string, mixed> $payload */
    private function signature(array $payload): string
    {
        return hash_hmac('sha512', json_encode($payload, JSON_THROW_ON_ERROR), 'webhook-secret');
    }
}
