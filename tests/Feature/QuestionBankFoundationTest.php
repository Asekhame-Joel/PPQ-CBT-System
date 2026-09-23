<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class QuestionBankFoundationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_question_belongs_to_course_and_returns_options_in_display_order(): void
    {
        $course = Course::factory()->create();
        $question = Question::factory()->for($course)->create();
        QuestionOption::factory()->for($question)->create([
            'option_text' => 'Third option',
            'sort_order' => 3,
        ]);
        QuestionOption::factory()->for($question)->correct()->create([
            'option_text' => 'First option',
            'sort_order' => 1,
        ]);

        $question->load('options');

        $this->assertTrue($question->course->is($course));
        $this->assertTrue($course->questions->contains($question));
        $this->assertSame(['First option', 'Third option'], $question->options->pluck('option_text')->all());
        $this->assertTrue($question->options->first()->is_correct);
    }

    public function test_active_scope_excludes_inactive_questions(): void
    {
        $activeQuestion = Question::factory()->create();
        Question::factory()->inactive()->create();

        $question = Question::active()->sole();

        $this->assertTrue($question->is($activeQuestion));
    }
}
