<?php

namespace App\Filament\Student\Widgets;

use App\Enums\AttemptStatus;
use App\Models\QuizAttempt;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

class StudentAttemptActivity extends Widget
{
    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.student.widgets.student-attempt-activity';

    /** @return Collection<int, QuizAttempt> */
    #[Computed]
    public function activeAttempts(): Collection
    {
        return QuizAttempt::query()
            ->where('user_id', auth()->id())
            ->where('status', AttemptStatus::InProgress)
            ->where('expires_at', '>', now())
            ->with('course:id,code,name')
            ->withCount([
                'questions as answered_count' => fn ($query) => $query
                    ->whereHas('answer', fn ($query) => $query->whereNotNull('selected_option_id')),
            ])
            ->latest('started_at')
            ->limit(3)
            ->get();
    }

    /** @return Collection<int, QuizAttempt> */
    #[Computed]
    public function recentAttempts(): Collection
    {
        return QuizAttempt::query()
            ->where('user_id', auth()->id())
            ->whereIn('status', [AttemptStatus::Submitted->value, AttemptStatus::Expired->value])
            ->whereNotNull('submitted_at')
            ->with('course:id,code,name')
            ->latest('submitted_at')
            ->limit(5)
            ->get();
    }
}
