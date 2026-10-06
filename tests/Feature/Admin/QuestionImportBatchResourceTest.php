<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\QuestionImportBatches\Pages\ManageQuestionImportBatches;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionImportBatchResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_delete_an_uploaded_question_batch_without_removing_attempt_snapshots(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create();
        $batch = QuestionImportBatch::factory()->for($course)->for($admin, 'importer')->create([
            'source_filename' => 'biology-revision.docx',
            'source_format' => 'docx',
            'question_count' => 2,
        ]);
        $question = Question::factory()->for($course)->for($batch, 'importBatch')->create([
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
        $secondQuestion = Question::factory()->for($course)->for($batch, 'importBatch')->create();
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
            ->test(ManageQuestionImportBatches::class)
            ->assertCanSeeTableRecords([$batch])
            ->callAction(TestAction::make('deleteBatch')->table($batch), ['confirmation' => 'DELETE'])
            ->assertHasNoActionErrors()
            ->assertNotified('2 live question(s) deleted');

        $this->assertModelMissing($batch);
        $this->assertModelMissing($question);
        $this->assertModelMissing($secondQuestion);
        $this->assertDatabaseCount('question_options', 0);

        $attemptQuestion->refresh();
        $this->assertNull($attemptQuestion->question_id);
        $this->assertSame('Which organ pumps blood around the body?', $attemptQuestion->question_snapshot);
        $this->assertSame('The heart pumps blood around the body.', $attemptQuestion->explanation_snapshot);
        $this->assertSame('Heart', $attemptQuestion->optionText($correctOption->id));
        $this->assertModelExists($attemptQuestion->answer);
    }
}
