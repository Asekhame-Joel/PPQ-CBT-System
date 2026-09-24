<?php

namespace App\Filament\Student\Pages;

use App\Enums\AttemptStatus;
use App\Models\QuizAttempt;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;

class PracticeResult extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'attempts/{attempt}/results';

    protected string $view = 'filament.student.pages.practice-result';

    public int $attemptId;

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
                'questions:id,quiz_attempt_id,position,question_snapshot,options_snapshot,correct_option_snapshot,explanation_snapshot',
                'questions.answer:id,attempt_question_id,selected_option_id,is_correct',
            ])
            ->sole();
    }

    public function getHeading(): string
    {
        return 'Practice Results';
    }
}
