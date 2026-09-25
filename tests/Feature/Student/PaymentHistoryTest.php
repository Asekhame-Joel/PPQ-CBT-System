<?php

namespace Tests\Feature\Student;

use App\Filament\Student\Pages\PaymentHistory;
use App\Filament\Student\Pages\PaymentReceipt;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentHistoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_sees_only_their_own_payment_history(): void
    {
        $student = User::factory()->create();
        $ownPayments = Payment::factory()->count(2)->for($student)->create();
        $otherPayment = Payment::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PaymentHistory::class)
            ->assertCanSeeTableRecords($ownPayments)
            ->assertCanNotSeeTableRecords([$otherPayment]);
    }

    public function test_student_can_view_a_receipt_for_their_successful_payment(): void
    {
        $student = User::factory()->create(['name' => 'Ada Student']);
        $payment = Payment::factory()->successful()->for($student)->create([
            'reference' => 'EXAM-RECEIPT-001',
            'amount' => '2500.00',
        ]);
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PaymentReceipt::class, ['payment' => $payment])
            ->assertSee('Payment receipt')
            ->assertSee('Ada Student')
            ->assertSee('EXAM-RECEIPT-001')
            ->assertSee('2,500.00')
            ->assertSee('Print receipt');
    }

    public function test_student_cannot_view_a_receipt_for_a_pending_payment(): void
    {
        $student = User::factory()->create();
        $payment = Payment::factory()->for($student)->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PaymentReceipt::class, ['payment' => $payment])
            ->assertNotFound();
    }

    public function test_student_cannot_view_another_students_receipt(): void
    {
        $student = User::factory()->create();
        $payment = Payment::factory()->successful()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PaymentReceipt::class, ['payment' => $payment])
            ->assertNotFound();
    }

    public function test_admin_cannot_open_student_payment_history(): void
    {
        $admin = User::factory()->admin()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->actingAs($admin)
            ->get(PaymentHistory::getUrl(panel: 'student'))
            ->assertForbidden();
    }
}
