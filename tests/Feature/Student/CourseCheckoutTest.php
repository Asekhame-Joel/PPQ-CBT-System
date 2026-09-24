<?php

namespace Tests\Feature\Student;

use App\Enums\AccessSource;
use App\Enums\PaymentStatus;
use App\Filament\Student\Pages\Checkout;
use App\Filament\Student\Pages\PaymentReturn;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Payment;
use App\Models\Question;
use App\Models\User;
use App\Payments\PaymentGateway;
use App\Payments\PaymentInitialization;
use App\Payments\PaymentVerification;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class CourseCheckoutTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_can_initialize_payment_for_an_eligible_ready_course(): void
    {
        [$student, $course] = $this->eligibleReadyCourse();
        $this->mock(PaymentGateway::class, function (MockInterface $mock) use ($student, $course): void {
            $mock->shouldReceive('initialize')->once()
                ->withArgs(fn (Payment $payment, string $callbackUrl): bool => $payment->user->is($student)
                    && $payment->course_id === $course->id
                    && $payment->amount === '1500.00'
                    && str_contains($callbackUrl, '/student/payments/'))
                ->andReturn(new PaymentInitialization('https://checkout.paystack.com/test-code', ['status' => true]));
        });
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(Checkout::class, ['course' => $course])
            ->call('pay')
            ->assertRedirect('https://checkout.paystack.com/test-code');

        $payment = Payment::query()->sole();
        $this->assertSame($student->id, $payment->user_id);
        $this->assertSame($course->id, $payment->course_id);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame(['status' => true], $payment->provider_response);
    }

    public function test_student_cannot_checkout_an_ineligible_course(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();
        Question::factory()->count($course->min_question_count)->for($course)->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)->test(Checkout::class, ['course' => $course])->assertNotFound();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_student_cannot_checkout_a_course_they_already_own(): void
    {
        [$student, $course] = $this->eligibleReadyCourse();
        CourseAccess::factory()->for($student)->for($course)->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)->test(Checkout::class, ['course' => $course])->assertNotFound();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_verified_payment_grants_course_access(): void
    {
        [$student, $course] = $this->eligibleReadyCourse();
        $payment = Payment::factory()->for($student)->for($course)->create([
            'amount' => '1500.00', 'currency' => 'NGN', 'reference' => 'EXAM-VERIFY-001',
        ]);
        $this->mock(PaymentGateway::class, function (MockInterface $mock): void {
            $mock->shouldReceive('verify')->once()->with('EXAM-VERIFY-001')
                ->andReturn(new PaymentVerification(true, 'EXAM-VERIFY-001', 150000, 'NGN', [
                    'data' => ['status' => 'success'],
                ]));
        });
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PaymentReturn::class, ['payment' => $payment])
            ->assertSee('Payment successful');

        $payment->refresh();
        $access = CourseAccess::query()->sole();
        $this->assertSame(PaymentStatus::Successful, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame($student->id, $access->user_id);
        $this->assertSame($course->id, $access->course_id);
        $this->assertSame($payment->id, $access->payment_id);
        $this->assertSame(AccessSource::Payment, $access->access_source);
    }

    public function test_mismatched_verified_amount_does_not_grant_access(): void
    {
        [$student, $course] = $this->eligibleReadyCourse();
        $payment = Payment::factory()->for($student)->for($course)->create([
            'amount' => '1500.00', 'currency' => 'NGN', 'reference' => 'EXAM-VERIFY-002',
        ]);
        $this->mock(PaymentGateway::class, function (MockInterface $mock): void {
            $mock->shouldReceive('verify')->once()
                ->andReturn(new PaymentVerification(true, 'EXAM-VERIFY-002', 100, 'NGN', [
                    'data' => ['status' => 'success'],
                ]));
        });
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PaymentReturn::class, ['payment' => $payment])
            ->assertSee('Payment not confirmed');

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertDatabaseCount('course_access', 0);
    }

    public function test_student_cannot_view_another_students_payment_return(): void
    {
        $student = User::factory()->create();
        $payment = Payment::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)->test(PaymentReturn::class, ['payment' => $payment])->assertNotFound();
    }

    /** @return array{User, Course} */
    private function eligibleReadyCourse(): array
    {
        $student = User::factory()->create();
        $course = Course::factory()->create([
            'level_id' => $student->level_id,
            'price' => '1500.00',
            'min_question_count' => 2,
        ]);
        $course->departments()->attach($student->department_id);
        Question::factory()->count(2)->for($course)->create();

        return [$student, $course];
    }
}
