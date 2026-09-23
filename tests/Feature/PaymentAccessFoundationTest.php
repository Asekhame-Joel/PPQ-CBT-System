<?php

namespace Tests\Feature;

use App\Enums\AccessSource;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PaymentAccessFoundationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_successful_payment_preserves_transaction_details_and_relationships(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();
        $payment = Payment::factory()
            ->for($student)
            ->for($course)
            ->successful()
            ->create(['amount' => 1000]);

        $this->assertTrue($payment->user->is($student));
        $this->assertTrue($payment->course->is($course));
        $this->assertSame('1000.00', $payment->amount);
        $this->assertSame(PaymentStatus::Successful, $payment->status);
        $this->assertSame(PaymentProvider::Paystack, $payment->provider);
        $this->assertSame(['status' => 'success'], $payment->provider_response);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_payment_reference_cannot_be_processed_twice(): void
    {
        Payment::factory()->create(['reference' => 'PAY-UNIQUE-001']);

        $this->expectException(UniqueConstraintViolationException::class);

        Payment::factory()->create(['reference' => 'PAY-UNIQUE-001']);
    }

    public function test_available_course_access_excludes_inactive_and_expired_records(): void
    {
        $this->travelTo('2026-09-23 12:00:00');
        $availableAccess = CourseAccess::factory()->create();
        CourseAccess::factory()->inactive()->create();
        CourseAccess::factory()->create(['expires_at' => now()->subMinute()]);

        $access = CourseAccess::available()->sole();

        $this->assertTrue($access->is($availableAccess));
        $this->assertSame(AccessSource::Admin, $access->access_source);
    }

    public function test_paid_access_links_to_its_payment_student_and_course(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();
        $payment = Payment::factory()
            ->for($student)
            ->for($course)
            ->successful()
            ->create();
        $access = CourseAccess::factory()
            ->for($student)
            ->for($course)
            ->for($payment)
            ->create(['access_source' => AccessSource::Payment]);

        $this->assertTrue($access->payment->is($payment));
        $this->assertTrue($student->courseAccesses->contains($access));
        $this->assertTrue($course->courseAccesses->contains($access));
        $this->assertTrue($payment->courseAccesses->contains($access));
    }
}
