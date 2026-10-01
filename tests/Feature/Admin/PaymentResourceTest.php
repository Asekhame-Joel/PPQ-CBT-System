<?php

namespace Tests\Feature\Admin;

use App\Enums\AccessSource;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\Pages\CreatePayment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Payment;
use App\Models\User;
use App\Payments\ApprovePendingPayment;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_list_search_and_filter_payments(): void
    {
        $admin = User::factory()->admin()->create();
        $successfulPayment = Payment::factory()->successful()->create([
            'reference' => 'EXAM-ADMIN-001',
        ]);
        $pendingPayment = Payment::factory()->create([
            'reference' => 'EXAM-ADMIN-002',
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListPayments::class)
            ->assertCanSeeTableRecords([$successfulPayment, $pendingPayment])
            ->searchTable('EXAM-ADMIN-001')
            ->assertCanSeeTableRecords([$successfulPayment])
            ->assertCanNotSeeTableRecords([$pendingPayment])
            ->searchTable()
            ->filterTable('status', PaymentStatus::Successful->value)
            ->assertCanSeeTableRecords([$successfulPayment])
            ->assertCanNotSeeTableRecords([$pendingPayment]);
    }

    public function test_admin_can_create_a_manual_payment_record(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreatePayment::class)
            ->fillForm([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'reference' => 'EXAM-MANUAL-RECORD-001',
                'amount' => 1500,
                'currency' => 'NGN',
                'status' => PaymentStatus::Pending->value,
                'provider' => 'paystack',
                'paid_at' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('payments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'reference' => 'EXAM-MANUAL-RECORD-001',
            'status' => PaymentStatus::Pending->value,
        ]);
        $this->assertArrayHasKey('create', PaymentResource::getPages());
        $this->assertArrayHasKey('edit', PaymentResource::getPages());
    }

    public function test_admin_can_approve_pending_payment_and_unlock_course(): void
    {
        $admin = User::factory()->admin()->create();
        $payment = Payment::factory()->create([
            'reference' => 'EXAM-MANUAL-001',
            'provider_response' => ['status' => 'pending'],
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListPayments::class)
            ->assertActionVisible(TestAction::make('approve')->table($payment))
            ->callAction(TestAction::make('approve')->table($payment))
            ->assertHasNoActionErrors()
            ->assertNotified('Payment approved and course unlocked');

        $payment->refresh();
        $access = CourseAccess::query()->sole();
        $this->assertSame(PaymentStatus::Successful, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame($admin->id, $payment->provider_response['manual_approval']['admin_id']);
        $this->assertSame($payment->user_id, $access->user_id);
        $this->assertSame($payment->course_id, $access->course_id);
        $this->assertSame($payment->id, $access->payment_id);
        $this->assertSame(AccessSource::Payment, $access->access_source);
        $this->assertTrue($access->is_active);
    }

    public function test_admin_editing_a_payment_to_successful_unlocks_the_course(): void
    {
        $admin = User::factory()->admin()->create();
        $payment = Payment::factory()->create(['status' => PaymentStatus::Pending]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(EditPayment::class, ['record' => $payment->id])
            ->fillForm(['status' => PaymentStatus::Successful->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $access = CourseAccess::query()->sole();
        $this->assertTrue($access->is_active);
        $this->assertSame($payment->id, $access->payment_id);
        $this->assertSame(PaymentStatus::Successful, $payment->fresh()->status);
    }

    public function test_admin_editing_a_payment_to_failed_locks_its_course_access(): void
    {
        $admin = User::factory()->admin()->create();
        $payment = Payment::factory()->successful()->create();
        $access = CourseAccess::factory()->for($payment->user)->for($payment->course)->for($payment)->create([
            'access_source' => AccessSource::Payment,
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(EditPayment::class, ['record' => $payment->id])
            ->fillForm(['status' => PaymentStatus::Failed->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(PaymentStatus::Failed, $payment->fresh()->status);
        $this->assertFalse($access->fresh()->is_active);
    }

    public function test_manual_approval_is_not_available_for_a_completed_payment(): void
    {
        $admin = User::factory()->admin()->create();
        $payment = Payment::factory()->successful()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListPayments::class)
            ->assertActionHidden(TestAction::make('approve')->table($payment));
    }

    public function test_non_admin_cannot_approve_payment_through_the_domain_action(): void
    {
        $student = User::factory()->create();
        $payment = Payment::factory()->create();

        try {
            app(ApprovePendingPayment::class)->handle($payment, $student);
            $this->fail('A student unexpectedly approved a payment.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Only an administrator can approve a pending payment.',
                $exception->errors()['payment'][0],
            );
        }

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertDatabaseCount('course_access', 0);
    }

    public function test_student_cannot_open_admin_payment_register(): void
    {
        $student = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs($student)
            ->get(PaymentResource::getUrl('index', panel: 'admin'))
            ->assertForbidden();
    }
}
