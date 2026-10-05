<?php

namespace Tests\Feature;

use App\Enums\CourseQuestionRequestStatus;
use App\Filament\Student\Pages\RequestCourseQuestions;
use App\Models\CourseQuestionRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentCourseQuestionRequestTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_can_request_a_course_question_bank(): void
    {
        $student = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(RequestCourseQuestions::class)
            ->fillForm([
                'course_code' => 'mth 101',
                'course_name' => 'Elementary Mathematics I',
                'message' => 'Please add questions for 100 level science students.',
            ])
            ->call('submitRequest')
            ->assertHasNoErrors()
            ->assertNotified('Course request sent');

        $this->assertDatabaseHas('course_question_requests', [
            'user_id' => $student->id,
            'course_code' => 'MTH 101',
            'course_name' => 'Elementary Mathematics I',
            'status' => CourseQuestionRequestStatus::Requested->value,
        ]);
    }

    public function test_student_only_sees_their_own_course_requests(): void
    {
        $student = User::factory()->create();
        CourseQuestionRequest::factory()->for($student, 'student')->create([
            'course_code' => 'GST 111',
            'course_name' => 'Communication in English I',
            'admin_notes' => 'We are working on this question bank.',
        ]);
        CourseQuestionRequest::factory()->create([
            'course_code' => 'PHY 101',
            'course_name' => 'General Physics I',
        ]);
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(RequestCourseQuestions::class)
            ->assertSee('GST 111')
            ->assertSee('We are working on this question bank.')
            ->assertDontSee('PHY 101');
    }
}
