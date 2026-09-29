<?php

namespace Tests\Feature;

use App\Enums\SupportRequestCategory;
use App\Enums\SupportRequestStatus;
use App\Filament\Student\Pages\HelpCentre;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Payment;
use App\Models\SupportRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentHelpCentreTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_a_payment_rectification_request(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();
        CourseAccess::factory()->for($student)->for($course)->create();
        $payment = Payment::factory()->for($student)->for($course)->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(HelpCentre::class)
            ->fillForm([
                'category' => SupportRequestCategory::PaymentRectification->value,
                'course_id' => $course->id,
                'payment_id' => $payment->id,
                'subject' => 'Payment is pending',
                'message' => 'I paid but my course is still locked.',
            ])
            ->call('submitRequest')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('support_requests', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'payment_id' => $payment->id,
            'category' => SupportRequestCategory::PaymentRectification->value,
            'status' => SupportRequestStatus::Open->value,
        ]);
    }

    public function test_help_centre_shows_only_the_signed_in_students_requests(): void
    {
        $student = User::factory()->create();
        SupportRequest::factory()->create([
            'user_id' => $student->id,
            'subject' => 'My account name is incorrect',
        ]);
        SupportRequest::factory()->create(['subject' => 'Another students request']);
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(HelpCentre::class)
            ->assertSee('Help, corrections, and payment issues.')
            ->assertSee('My account name is incorrect')
            ->assertDontSee('Another students request');
    }
}
