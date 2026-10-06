<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Questions\Pages\CreateQuestion;
use App\Filament\Resources\Questions\Pages\EditQuestion;
use App\Filament\Resources\Questions\Pages\ListQuestions;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_list_questions(): void
    {
        $admin = User::factory()->admin()->create();
        $questions = Question::factory()->count(3)->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListQuestions::class)
            ->assertCanSeeTableRecords($questions);
    }

    public function test_admin_can_create_question_with_ordered_answer_options(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateQuestion::class)
            ->fillForm($this->validQuestionData($course))
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $question = Question::where('question_text', 'What does CPU stand for?')->sole();

        $this->assertTrue($question->course->is($course));
        $this->assertCount(3, $question->options);
        $this->assertSame([
            'Central Processing Unit',
            'Computer Processing Utility',
            'Central Program Utility',
        ], $question->options->pluck('option_text')->all());
        $this->assertSame(1, $question->options->where('is_correct', true)->count());
    }

    public function test_admin_can_update_question_and_replace_answer_options(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $question = Question::factory()->for($course)->create();
        QuestionOption::factory()->for($question)->create(['sort_order' => 1]);
        QuestionOption::factory()->for($question)->correct()->create(['sort_order' => 2]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(EditQuestion::class, ['record' => $question->id])
            ->fillForm($this->validQuestionData($course))
            ->call('save')
            ->assertHasNoFormErrors();

        $question->refresh();

        $this->assertSame('What does CPU stand for?', $question->question_text);
        $this->assertCount(3, $question->options);
        $this->assertSame(1, $question->options->where('is_correct', true)->count());
    }

    public function test_question_requires_at_least_two_options(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $data = $this->validQuestionData($course);
        $data['options'] = [
            ['option_text' => 'Only option', 'is_correct' => true],
        ];
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateQuestion::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasFormErrors(['options' => 'min']);

        $this->assertDatabaseMissing('questions', ['question_text' => 'What does CPU stand for?']);
    }

    public function test_question_requires_exactly_one_correct_option(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $data = $this->validQuestionData($course);
        $data['options'][1]['is_correct'] = true;
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateQuestion::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasFormErrors([
                'options' => 'Exactly one answer option must be marked as correct.',
            ]);

        $this->assertDatabaseMissing('questions', ['question_text' => 'What does CPU stand for?']);
    }

    public function test_admin_can_delete_all_live_questions_without_removing_attempt_snapshots(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create();
        $question = Question::factory()->for($course)->create([
            'question_text' => 'Which organ pumps blood around the body?',
            'explanation' => 'The heart pumps blood around the body.',
        ]);
        $correctOption = QuestionOption::factory()->for($question)->correct()->create([
            'option_text' => 'Heart',
            'sort_order' => 1,
        ]);
        QuestionOption::factory()->for($question)->create([
            'option_text' => 'Lungs',
            'sort_order' => 2,
        ]);
        $attempt = QuizAttempt::factory()->submitted()->for($student)->for($course)->create([
            'question_count' => 1,
        ]);
        $attemptQuestion = AttemptQuestion::factory()->for($attempt)->for($question)->create([
            'position' => 1,
            'question_snapshot' => $question->question_text,
            'options_snapshot' => [
                ['id' => $correctOption->id, 'text' => 'Heart'],
                ['id' => $correctOption->id + 1, 'text' => 'Lungs'],
            ],
            'correct_option_snapshot' => $correctOption->id,
            'explanation_snapshot' => $question->explanation,
        ]);
        AttemptAnswer::factory()->for($attemptQuestion)->create([
            'selected_option_id' => $correctOption->id,
            'is_correct' => true,
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListQuestions::class)
            ->callAction(TestAction::make('deleteAllQuestions'), ['confirmation' => 'DELETE'])
            ->assertHasNoActionErrors()
            ->assertNotified('1 question(s) deleted');

        $this->assertModelMissing($question);
        $this->assertDatabaseCount('question_options', 0);

        $attemptQuestion->refresh();
        $this->assertNull($attemptQuestion->question_id);
        $this->assertSame('Which organ pumps blood around the body?', $attemptQuestion->question_snapshot);
        $this->assertSame('The heart pumps blood around the body.', $attemptQuestion->explanation_snapshot);
        $this->assertSame('Heart', $attemptQuestion->optionText($correctOption->id));
        $this->assertModelExists($attemptQuestion->answer);
    }

    /**
     * @return array<string, mixed>
     */
    private function validQuestionData(Course $course): array
    {
        return [
            'course_id' => $course->id,
            'question_text' => 'What does CPU stand for?',
            'explanation' => 'CPU means Central Processing Unit.',
            'is_active' => true,
            'options' => [
                ['option_text' => 'Central Processing Unit', 'is_correct' => true],
                ['option_text' => 'Computer Processing Utility', 'is_correct' => false],
                ['option_text' => 'Central Program Utility', 'is_correct' => false],
            ],
        ];
    }
}
