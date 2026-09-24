<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Questions\Pages\ImportQuestions;
use App\Models\Course;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionImportTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_preview_and_import_aiken_questions(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'questions.txt',
            "What is the capital of Nigeria?\nA. Lagos\nB. Abuja\nANSWER: B\n\n2 + 2 equals?\nA. 3\nB. 4\nANSWER: B",
        );
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $component = Livewire::actingAs($admin)
            ->test(ImportQuestions::class)
            ->fillForm([
                'course_id' => $course->id,
                'file' => $file,
            ])
            ->call('preview')
            ->assertHasNoFormErrors()
            ->assertSet('hasPreview', true)
            ->assertCount('previewQuestions', 2)
            ->assertSet('previewErrors', []);

        $component
            ->call('import')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseCount('questions', 2);
        $this->assertDatabaseCount('question_options', 4);
        $this->assertDatabaseHas('questions', [
            'course_id' => $course->id,
            'question_text' => 'What is the capital of Nigeria?',
        ]);
        $this->assertDatabaseHas('question_options', [
            'option_text' => 'Abuja',
            'is_correct' => true,
            'sort_order' => 2,
        ]);
    }

    public function test_invalid_file_is_previewed_but_cannot_be_imported(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'questions.txt',
            "Question without an answer?\nA. Yes\nB. No",
        );
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ImportQuestions::class)
            ->fillForm([
                'course_id' => $course->id,
                'file' => $file,
            ])
            ->call('preview')
            ->assertSet('hasPreview', true)
            ->assertSet('previewQuestions', [])
            ->assertSet('previewErrors.0', 'Question 1: the ANSWER line is missing.')
            ->call('import')
            ->assertNoRedirect();

        $this->assertDatabaseCount('questions', 0);
    }
}
