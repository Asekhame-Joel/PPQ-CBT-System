<?php

namespace Tests\Feature\Practice;

use App\Enums\AttemptStatus;
use App\Enums\AttemptType;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Practice\StartPracticeAttempt;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

class StartPracticeAttemptTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_creates_a_timed_attempt_with_immutable_question_snapshots(): void
    {
        Carbon::setTestNow('2026-09-24 12:00:00');
        [$student, $course] = $this->studentWithAccess([
            'min_question_count' => 2,
            'max_question_count' => 10,
            'min_duration' => 10,
            'max_duration' => 60,
        ]);
        $first = $this->createValidQuestion($course, 'First question?', 'First explanation');
        $second = $this->createValidQuestion($course, 'Second question?', 'Second explanation');
        $this->createValidQuestion($course, 'Inactive question?')->update(['is_active' => false]);
        $firstCorrectOption = $first->options()->where('is_correct', true)->sole();

        $attempt = app(StartPracticeAttempt::class)->handle(
            $student,
            $course,
            questionCount: 2,
            durationMinutes: 30,
            randomizeQuestions: false,
            randomizeOptions: false,
        );

        $this->assertTrue($attempt->user->is($student));
        $this->assertTrue($attempt->course->is($course));
        $this->assertSame(AttemptType::NewPractice, $attempt->attempt_type);
        $this->assertSame(AttemptStatus::InProgress, $attempt->status);
        $this->assertTrue($attempt->started_at->equalTo(now()));
        $this->assertTrue($attempt->expires_at->equalTo(now()->addMinutes(30)));
        $this->assertSame([$first->id, $second->id], $attempt->questions->pluck('question_id')->all());
        $this->assertSame(['First question?', 'Second question?'], $attempt->questions->pluck('question_snapshot')->all());
        $this->assertSame('First explanation', $attempt->questions->first()->explanation_snapshot);
        $this->assertCount(2, $attempt->questions->first()->options_snapshot);
        $this->assertSame($firstCorrectOption->id, $attempt->questions->first()->correct_option_snapshot);
        $this->assertNotNull($attempt->questions->first()->answer);
        $this->assertNull($attempt->questions->first()->answer->selected_option_id);

        Carbon::setTestNow();
    }

    public function test_it_rechecks_course_access_before_creating_an_attempt(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['min_question_count' => 1]);
        $this->createValidQuestion($course, 'Question?');

        try {
            app(StartPracticeAttempt::class)->handle($student, $course, 1, 30, true, true);
            $this->fail('A validation exception was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('Course access is no longer available.', $exception->errors()['course'][0]);
        }

        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_it_rejects_counts_above_the_number_of_active_questions(): void
    {
        [$student, $course] = $this->studentWithAccess([
            'min_question_count' => 1,
            'max_question_count' => 10,
        ]);
        $this->createValidQuestion($course, 'Only question?');

        try {
            app(StartPracticeAttempt::class)->handle($student, $course, 2, 30, true, true);
            $this->fail('A validation exception was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('Choose between 1 and 1 questions.', $exception->errors()['question_count'][0]);
        }

        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_invalid_answer_set_rolls_back_the_whole_attempt(): void
    {
        [$student, $course] = $this->studentWithAccess(['min_question_count' => 1]);
        $question = Question::factory()->for($course)->create();
        QuestionOption::factory()->for($question)->create([
            'sort_order' => 1,
            'is_correct' => true,
        ]);

        $this->expectException(LogicException::class);

        try {
            app(StartPracticeAttempt::class)->handle($student, $course, 1, 30, false, false);
        } finally {
            $this->assertDatabaseCount('quiz_attempts', 0);
            $this->assertDatabaseCount('attempt_questions', 0);
            $this->assertDatabaseCount('attempt_answers', 0);
        }
    }

    /** @param array<string, mixed> $courseAttributes
     * @return array{User, Course}
     */
    private function studentWithAccess(array $courseAttributes = []): array
    {
        $student = User::factory()->create();
        $course = Course::factory()->create($courseAttributes);
        CourseAccess::factory()->for($student)->for($course)->create();

        return [$student, $course];
    }

    private function createValidQuestion(Course $course, string $text, ?string $explanation = null): Question
    {
        $question = Question::factory()->for($course)->create([
            'question_text' => $text,
            'explanation' => $explanation,
        ]);
        QuestionOption::factory()->for($question)->create([
            'sort_order' => 1,
            'is_correct' => false,
        ]);
        QuestionOption::factory()->for($question)->create([
            'sort_order' => 2,
            'is_correct' => true,
        ]);

        return $question;
    }
}
