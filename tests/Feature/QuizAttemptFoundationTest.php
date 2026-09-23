<?php

namespace Tests\Feature;

use App\Enums\AttemptStatus;
use App\Enums\AttemptType;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class QuizAttemptFoundationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_attempt_belongs_to_student_and_course_and_supports_retries(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();
        $original = QuizAttempt::factory()->for($student)->for($course)->create();
        $retry = QuizAttempt::factory()->for($student)->for($course)->create([
            'parent_attempt_id' => $original->id,
            'attempt_type' => AttemptType::StartAgain,
        ]);

        $this->assertTrue($original->user->is($student));
        $this->assertTrue($original->course->is($course));
        $this->assertTrue($retry->parentAttempt->is($original));
        $this->assertTrue($original->childAttempts->contains($retry));
        $this->assertSame(AttemptStatus::InProgress, $original->status);
        $this->assertSame(AttemptType::StartAgain, $retry->attempt_type);
    }

    public function test_question_snapshot_survives_source_question_deletion(): void
    {
        $question = Question::factory()->create(['question_text' => 'Original question']);
        $correctOption = QuestionOption::factory()->for($question)->correct()->create();
        $attemptQuestion = AttemptQuestion::factory()->create([
            'question_id' => $question->id,
            'position' => 1,
            'question_snapshot' => 'Original question',
            'options_snapshot' => [
                ['id' => $correctOption->id, 'text' => $correctOption->option_text],
            ],
            'correct_option_snapshot' => $correctOption->id,
        ]);

        $question->delete();
        $attemptQuestion->refresh();

        $this->assertNull($attemptQuestion->question_id);
        $this->assertSame('Original question', $attemptQuestion->question_snapshot);
        $this->assertSame($correctOption->id, $attemptQuestion->correct_option_snapshot);
        $this->assertSame($correctOption->option_text, $attemptQuestion->options_snapshot[0]['text']);
    }

    public function test_attempt_answer_links_to_snapshot_and_uses_expected_types(): void
    {
        $attemptQuestion = AttemptQuestion::factory()->create();
        $answer = AttemptAnswer::factory()->for($attemptQuestion)->create([
            'selected_option_id' => 101,
            'is_correct' => true,
        ]);

        $this->assertTrue($answer->attemptQuestion->is($attemptQuestion));
        $this->assertTrue($attemptQuestion->answer->is($answer));
        $this->assertSame(101, $answer->selected_option_id);
        $this->assertTrue($answer->is_correct);
    }
}
