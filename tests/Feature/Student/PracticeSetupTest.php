<?php

namespace Tests\Feature\Student;

use App\Filament\Student\Pages\PracticeSetup;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PracticeSetupTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_can_open_setup_for_an_unlocked_course(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create([
            'code' => 'CSC101',
            'default_question_count' => 50,
            'min_question_count' => 10,
            'max_question_count' => 100,
            'default_duration' => 30,
        ]);
        CourseAccess::factory()->for($student)->for($course)->create();
        Question::factory()->count(20)->for($course)->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeSetup::class, ['course' => $course])
            ->assertSee('Practice CSC101')
            ->assertSee('20 questions available')
            ->assertFormSet([
                'question_count' => 20,
                'duration_minutes' => 30,
                'randomize_questions' => true,
                'randomize_options' => true,
            ]);
    }

    public function test_student_can_save_valid_practice_preferences(): void
    {
        [$student, $course] = $this->studentWithCourseAccess();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeSetup::class, ['course' => $course])
            ->fillForm([
                'question_count' => 15,
                'duration_minutes' => 25,
                'randomize_questions' => false,
                'randomize_options' => true,
            ])
            ->call('saveSettings')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('student_settings', [
            'user_id' => $student->id,
            'preferred_question_count' => 15,
            'preferred_duration' => 25,
            'randomize_questions' => false,
            'randomize_options' => true,
        ]);
    }

    public function test_question_count_and_duration_must_remain_inside_course_limits(): void
    {
        [$student, $course] = $this->studentWithCourseAccess();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeSetup::class, ['course' => $course])
            ->fillForm([
                'question_count' => 31,
                'duration_minutes' => 121,
            ])
            ->call('saveSettings')
            ->assertHasFormErrors([
                'question_count' => 'max',
                'duration_minutes' => 'max',
            ]);

        $this->assertDatabaseMissing('student_settings', ['user_id' => $student->id]);
    }

    public function test_start_practice_creates_an_attempt_and_redirects_to_the_session(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create([
            'min_question_count' => 2,
            'max_question_count' => 10,
            'min_duration' => 10,
            'max_duration' => 60,
        ]);
        CourseAccess::factory()->for($student)->for($course)->create();

        Question::factory()->count(2)->for($course)->create()->each(function (Question $question): void {
            QuestionOption::factory()->for($question)->create(['sort_order' => 1, 'is_correct' => false]);
            QuestionOption::factory()->for($question)->create(['sort_order' => 2, 'is_correct' => true]);
        });
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeSetup::class, ['course' => $course])
            ->fillForm([
                'question_count' => 2,
                'duration_minutes' => 20,
                'randomize_questions' => false,
                'randomize_options' => false,
            ])
            ->call('startPractice')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'question_count' => 2,
            'duration_minutes' => 20,
            'status' => 'in_progress',
        ]);
        $this->assertDatabaseCount('attempt_questions', 2);
        $this->assertDatabaseCount('attempt_answers', 2);
    }

    public function test_student_cannot_open_setup_without_current_course_access(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['min_question_count' => 2]);
        Question::factory()->count(2)->for($course)->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->actingAs($student)
            ->get(PracticeSetup::getUrl(['course' => $course], panel: 'student'))
            ->assertNotFound();
    }

    /** @return array{User, Course} */
    private function studentWithCourseAccess(): array
    {
        $student = User::factory()->create();
        $course = Course::factory()->create([
            'min_question_count' => 10,
            'max_question_count' => 30,
            'min_duration' => 10,
            'max_duration' => 120,
        ]);
        CourseAccess::factory()->for($student)->for($course)->create();
        Question::factory()->count(30)->for($course)->create();

        return [$student, $course];
    }
}
