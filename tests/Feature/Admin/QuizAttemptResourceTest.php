<?php

namespace Tests\Feature\Admin;

use App\Enums\AttemptStatus;
use App\Filament\Resources\QuizAttempts\Pages\ListQuizAttempts;
use App\Filament\Resources\QuizAttempts\Pages\ViewQuizAttempt;
use App\Filament\Resources\QuizAttempts\QuizAttemptResource;
use App\Filament\Resources\QuizAttempts\RelationManagers\QuestionsRelationManager;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuizAttemptResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_list_search_and_filter_practice_attempts(): void
    {
        $admin = User::factory()->admin()->create();
        $ada = User::factory()->create(['name' => 'Ada Student']);
        $tunde = User::factory()->create(['name' => 'Tunde Student']);
        $submitted = QuizAttempt::factory()->submitted()->for($ada)->create();
        $inProgress = QuizAttempt::factory()->for($tunde)->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListQuizAttempts::class)
            ->assertCanSeeTableRecords([$submitted, $inProgress])
            ->searchTable('Ada Student')
            ->assertCanSeeTableRecords([$submitted])
            ->assertCanNotSeeTableRecords([$inProgress])
            ->searchTable()
            ->filterTable('status', AttemptStatus::Submitted->value)
            ->assertCanSeeTableRecords([$submitted])
            ->assertCanNotSeeTableRecords([$inProgress]);
    }

    public function test_admin_can_view_result_summary_and_question_answers(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create(['name' => 'Result Student']);
        $course = Course::factory()->create(['code' => 'CSC401', 'name' => 'Advanced Computing']);
        $attempt = QuizAttempt::factory()->submitted()->for($student)->for($course)->create([
            'question_count' => 2,
            'correct_count' => 1,
            'incorrect_count' => 1,
            'unanswered_count' => 0,
            'score_percentage' => 50,
        ]);
        $question = AttemptQuestion::factory()->for($attempt)->create([
            'position' => 1,
            'question_snapshot' => 'What does CPU stand for?',
            'options_snapshot' => [
                ['id' => 101, 'text' => 'Central Processing Unit'],
                ['id' => 102, 'text' => 'Computer Personal Unit'],
            ],
            'correct_option_snapshot' => 101,
        ]);
        AttemptAnswer::factory()->for($question)->create([
            'selected_option_id' => 102,
            'is_correct' => false,
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ViewQuizAttempt::class, ['record' => $attempt->id])
            ->assertSee('Result Student')
            ->assertSee('CSC401')
            ->assertSee('50.00%')
            ->assertSee('Submitted');

        Livewire::actingAs($admin)
            ->test(QuestionsRelationManager::class, [
                'ownerRecord' => $attempt,
                'pageClass' => ViewQuizAttempt::class,
            ])
            ->assertCanSeeTableRecords([$question])
            ->assertSee('What does CPU stand for?')
            ->assertSee('Computer Personal Unit')
            ->assertSee('Central Processing Unit');
    }

    public function test_attempt_reporting_is_read_only(): void
    {
        $admin = User::factory()->admin()->create();
        $attempt = QuizAttempt::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($admin);

        $this->assertFalse(QuizAttemptResource::canCreate());
        $this->assertFalse(QuizAttemptResource::canEdit($attempt));
        $this->assertFalse(QuizAttemptResource::canDelete($attempt));
        $this->assertArrayNotHasKey('create', QuizAttemptResource::getPages());
        $this->assertArrayNotHasKey('edit', QuizAttemptResource::getPages());
    }

    public function test_student_cannot_open_admin_attempt_reporting(): void
    {
        $student = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs($student)
            ->get(QuizAttemptResource::getUrl('index', panel: 'admin'))
            ->assertForbidden();
    }
}
