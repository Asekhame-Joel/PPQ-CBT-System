<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\QuestionReports\Pages\EditQuestionReport;
use App\Filament\Resources\QuestionReports\Pages\ListQuestionReports;
use App\Models\QuestionReport;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionReportResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_list_question_reports(): void
    {
        $admin = User::factory()->admin()->create();
        $reports = QuestionReport::factory()->count(2)->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListQuestionReports::class)
            ->assertCanSeeTableRecords($reports);
    }

    public function test_admin_can_resolve_and_reopen_a_report(): void
    {
        $admin = User::factory()->admin()->create();
        $report = QuestionReport::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(EditQuestionReport::class, ['record' => $report->id])
            ->fillForm([
                'status' => 'resolved',
                'admin_notes' => 'The answer was corrected in the question bank.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $report->refresh();
        $this->assertSame('resolved', $report->status->value);
        $this->assertSame($admin->id, $report->resolved_by);
        $this->assertNotNull($report->resolved_at);

        Livewire::actingAs($admin)
            ->test(EditQuestionReport::class, ['record' => $report->id])
            ->fillForm([
                'status' => 'pending',
                'admin_notes' => 'Reopened for another review.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $report->refresh();
        $this->assertSame('pending', $report->status->value);
        $this->assertNull($report->resolved_by);
        $this->assertNull($report->resolved_at);
    }
}
