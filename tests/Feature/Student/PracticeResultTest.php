<?php

namespace Tests\Feature\Student;

use App\Enums\AttemptStatus;
use App\Filament\Student\Pages\PracticeResult;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PracticeResultTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_can_view_score_and_detailed_answer_review(): void
    {
        [$student, $attempt] = $this->completedAttempt();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeResult::class, ['attempt' => $attempt])
            ->assertSee('CSC101')
            ->assertSee('50.00%')
            ->assertSee('First question?')
            ->assertSee('First explanation')
            ->assertSee('Your answer · Correct')
            ->assertSee('Second question?')
            ->assertSee('Second explanation')
            ->assertSee('Your answer')
            ->assertSee('Correct answer');
    }

    public function test_student_cannot_view_another_students_result(): void
    {
        [, $attempt] = $this->completedAttempt();
        $otherStudent = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->actingAs($otherStudent)
            ->get(PracticeResult::getUrl(['attempt' => $attempt], panel: 'student'))
            ->assertNotFound();
    }

    public function test_in_progress_attempt_does_not_expose_results_or_explanations(): void
    {
        $student = User::factory()->create();
        $attempt = QuizAttempt::factory()->for($student)->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->actingAs($student)
            ->get(PracticeResult::getUrl(['attempt' => $attempt], panel: 'student'))
            ->assertNotFound();
    }

    /** @return array{User, QuizAttempt} */
    private function completedAttempt(): array
    {
        $student = User::factory()->create();
        $course = Course::factory()->create([
            'code' => 'CSC101',
            'name' => 'Introduction to Computing',
        ]);
        $attempt = QuizAttempt::factory()->for($student)->for($course)->create([
            'question_count' => 2,
            'status' => AttemptStatus::Submitted,
            'submitted_at' => now(),
            'correct_count' => 1,
            'incorrect_count' => 1,
            'unanswered_count' => 0,
            'score_percentage' => 50,
        ]);
        $first = $this->addQuestion($attempt, 1, 'First question?', 'First explanation');
        AttemptAnswer::factory()->for($first)->create([
            'selected_option_id' => 101,
            'is_correct' => true,
        ]);
        $second = $this->addQuestion($attempt, 2, 'Second question?', 'Second explanation');
        AttemptAnswer::factory()->for($second)->create([
            'selected_option_id' => 102,
            'is_correct' => false,
        ]);

        return [$student, $attempt];
    }

    private function addQuestion(
        QuizAttempt $attempt,
        int $position,
        string $text,
        string $explanation,
    ): AttemptQuestion {
        return AttemptQuestion::factory()->for($attempt)->create([
            'position' => $position,
            'question_snapshot' => $text,
            'options_snapshot' => [
                ['id' => 101, 'text' => 'Correct option'],
                ['id' => 102, 'text' => 'Wrong option'],
            ],
            'correct_option_snapshot' => 101,
            'explanation_snapshot' => $explanation,
        ]);

    }
}
