<?php

namespace App\Filament\Student\Pages;

use App\Enums\AttemptStatus;
use App\Enums\QuestionReportReason;
use App\Enums\QuestionReportStatus;
use App\Models\AttemptQuestion;
use App\Models\QuestionReport;
use App\Models\QuizAttempt;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;

class PracticeResult extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'attempts/{attempt}/results';

    protected string $view = 'filament.student.pages.practice-result';

    public int $attemptId;

    public ?int $reportingQuestionId = null;

    public string $reportReason = 'incorrect_answer';

    public string $reportComment = '';

    public function mount(QuizAttempt $attempt): void
    {
        abort_unless($attempt->user_id === auth()->id(), 404);
        abort_unless(in_array($attempt->status, [AttemptStatus::Submitted, AttemptStatus::Expired], true), 404);
        abort_if($attempt->submitted_at === null, 404);

        $this->attemptId = $attempt->id;
    }

    #[Computed]
    public function result(): QuizAttempt
    {
        return QuizAttempt::query()
            ->whereKey($this->attemptId)
            ->where('user_id', auth()->id())
            ->whereIn('status', [AttemptStatus::Submitted->value, AttemptStatus::Expired->value])
            ->with([
                'course:id,code,name',
                'questions:id,quiz_attempt_id,question_id,position,question_snapshot,options_snapshot,correct_option_snapshot,explanation_snapshot',
                'questions.answer:id,attempt_question_id,selected_option_id,is_correct',
            ])
            ->sole();
    }

    public function beginReport(int $attemptQuestionId): void
    {
        $question = $this->ownedAttemptQuestion($attemptQuestionId);
        $existing = QuestionReport::query()
            ->where('user_id', auth()->id())
            ->where('attempt_question_id', $question->id)
            ->first();

        if ($existing && $existing->status !== QuestionReportStatus::Pending) {
            Notification::make()
                ->title('This report has already been reviewed')
                ->warning()
                ->send();

            return;
        }

        $this->reportingQuestionId = $question->id;
        $this->reportReason = $existing?->reason?->value ?? QuestionReportReason::IncorrectAnswer->value;
        $this->reportComment = $existing?->report_text ?? '';
        $this->resetValidation();
    }

    public function cancelReport(): void
    {
        $this->reportingQuestionId = null;
        $this->reportComment = '';
        $this->resetValidation();
    }

    public function submitReport(): void
    {
        $data = $this->validate([
            'reportingQuestionId' => ['required', 'integer'],
            'reportReason' => ['required', 'in:'.implode(',', array_keys(QuestionReportReason::options()))],
            'reportComment' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        $question = $this->ownedAttemptQuestion($data['reportingQuestionId']);

        QuestionReport::query()->updateOrCreate([
            'user_id' => auth()->id(),
            'attempt_question_id' => $question->id,
        ], [
            'question_id' => $question->question_id,
            'reason' => $data['reportReason'],
            'report_text' => $data['reportComment'],
            'status' => QuestionReportStatus::Pending,
            'admin_notes' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ]);

        $this->cancelReport();

        Notification::make()
            ->title('Question report submitted')
            ->success()
            ->send();
    }

    private function ownedAttemptQuestion(int $attemptQuestionId): AttemptQuestion
    {
        return AttemptQuestion::query()
            ->whereKey($attemptQuestionId)
            ->whereHas('quizAttempt', fn ($query) => $query
                ->whereKey($this->attemptId)
                ->where('user_id', auth()->id()))
            ->firstOrFail();
    }

    public function getHeading(): string
    {
        return 'Practice Results';
    }
}
