<?php

namespace Tests\Feature\Student;

use App\Enums\AttemptStatus;
use App\Filament\Student\Pages\PracticeResult;
use App\Filament\Student\Pages\PracticeSession;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Practice\SaveAttemptAnswer;
use App\Practice\StartPracticeAttempt;
use App\Practice\SubmitPracticeAttempt;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PracticeSessionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_can_view_and_navigate_their_active_attempt(): void
    {
        [$student, $attempt] = $this->activeAttempt();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeSession::class, ['attempt' => $attempt])
            ->assertSee('First question?')
            ->assertDontSee('First explanation')
            ->assertSet('currentPosition', 1)
            ->call('nextQuestion')
            ->assertSet('currentPosition', 2)
            ->assertSee('Second question?')
            ->call('goTo', 1)
            ->assertSet('currentPosition', 1)
            ->call('previousQuestion')
            ->assertSet('currentPosition', 1);
    }

    public function test_selecting_an_option_autosaves_the_answer(): void
    {
        [$student, $attempt] = $this->activeAttempt();
        $attemptQuestion = $attempt->questions()->with('answer')->first();
        $optionId = $attemptQuestion->options_snapshot[1]['id'];
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeSession::class, ['attempt' => $attempt])
            ->call('selectAnswer', $attemptQuestion->id, $optionId)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('attempt_answers', [
            'attempt_question_id' => $attemptQuestion->id,
            'selected_option_id' => $optionId,
            'is_correct' => null,
        ]);
        $this->assertNotNull($attemptQuestion->answer->fresh()->answered_at);
    }

    public function test_answer_from_another_question_is_rejected(): void
    {
        [$student, $attempt] = $this->activeAttempt();
        $questions = $attempt->questions()->with('answer')->get();
        $foreignOptionId = $questions[1]->options_snapshot[0]['id'];
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeSession::class, ['attempt' => $attempt])
            ->call('selectAnswer', $questions[0]->id, $foreignOptionId)
            ->assertHasErrors(['answer']);

        $this->assertNull($questions[0]->answer->fresh()->selected_option_id);
    }

    public function test_late_answer_is_rejected_without_bypassing_expiry_scoring(): void
    {
        [$student, $attempt] = $this->activeAttempt();
        $question = $attempt->questions()->with('answer')->first();
        $attempt->update(['expires_at' => now()->subSecond()]);

        try {
            app(SaveAttemptAnswer::class)->handle(
                $student,
                $attempt->id,
                $question->id,
                $question->correct_option_snapshot,
            );
            $this->fail('A validation exception was not thrown.');
        } catch (ValidationException) {
            $this->assertSame(AttemptStatus::InProgress, $attempt->fresh()->status);
        }

        app(SubmitPracticeAttempt::class)->handle(
            $student,
            $attempt->id,
            requireExpired: true,
        );

        $this->assertSame(AttemptStatus::Expired, $attempt->fresh()->status);
        $this->assertSame(2, $attempt->fresh()->unanswered_count);
    }

    public function test_student_cannot_view_another_students_attempt(): void
    {
        [, $attempt] = $this->activeAttempt();
        $otherStudent = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->actingAs($otherStudent)
            ->get(PracticeSession::getUrl(['attempt' => $attempt], panel: 'student'))
            ->assertNotFound();
    }

    public function test_student_can_submit_and_score_the_attempt(): void
    {
        [$student, $attempt] = $this->activeAttempt();
        $question = $attempt->questions()->with('answer')->first();
        $correctOptionId = $question->correct_option_snapshot;
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeSession::class, ['attempt' => $attempt])
            ->call('selectAnswer', $question->id, $correctOptionId)
            ->call('submitAttempt')
            ->assertRedirect(PracticeResult::getUrl(['attempt' => $attempt], panel: 'student'));

        $attempt->refresh();

        $this->assertSame(AttemptStatus::Submitted, $attempt->status);
        $this->assertSame(1, $attempt->correct_count);
        $this->assertSame(0, $attempt->incorrect_count);
        $this->assertSame(1, $attempt->unanswered_count);
        $this->assertSame('50.00', $attempt->score_percentage);
    }

    public function test_opening_an_expired_attempt_scores_and_redirects_to_results(): void
    {
        [$student, $attempt] = $this->activeAttempt();
        $attempt->update(['expires_at' => now()->subSecond()]);
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->actingAs($student)
            ->get(PracticeSession::getUrl(['attempt' => $attempt], panel: 'student'))
            ->assertRedirect(PracticeResult::getUrl(['attempt' => $attempt], panel: 'student'));

        $this->assertSame(AttemptStatus::Expired, $attempt->fresh()->status);
        $this->assertNotNull($attempt->fresh()->submitted_at);
        $this->assertSame(2, $attempt->fresh()->unanswered_count);
    }

    /** @return array{User, QuizAttempt} */
    private function activeAttempt(): array
    {
        $student = User::factory()->create();
        $course = Course::factory()->create([
            'code' => 'CSC101',
            'min_question_count' => 2,
            'max_question_count' => 10,
            'min_duration' => 10,
            'max_duration' => 60,
        ]);
        CourseAccess::factory()->for($student)->for($course)->create();
        $this->createQuestion($course, 'First question?', 'First explanation');
        $this->createQuestion($course, 'Second question?', 'Second explanation');
        $attempt = app(StartPracticeAttempt::class)->handle($student, $course, 2, 30, false, false);

        return [$student, $attempt];
    }

    private function createQuestion(Course $course, string $text, string $explanation): void
    {
        $question = Question::factory()->for($course)->create([
            'question_text' => $text,
            'explanation' => $explanation,
        ]);
        QuestionOption::factory()->for($question)->create([
            'sort_order' => 1,
            'option_text' => 'Wrong',
            'is_correct' => false,
        ]);
        QuestionOption::factory()->for($question)->create([
            'sort_order' => 2,
            'option_text' => 'Correct',
            'is_correct' => true,
        ]);

    }
}
