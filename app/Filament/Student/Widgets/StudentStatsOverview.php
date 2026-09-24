<?php

namespace App\Filament\Student\Widgets;

use App\Enums\AttemptStatus;
use App\Filament\Student\Pages\MyCourses;
use App\Models\CourseAccess;
use App\Models\QuizAttempt;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();
        $unlockedCourses = CourseAccess::query()
            ->available()
            ->where('user_id', $userId)
            ->whereHas('course', fn ($query) => $query->active())
            ->count();
        $performance = QuizAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [AttemptStatus::Submitted->value, AttemptStatus::Expired->value])
            ->whereNotNull('score_percentage')
            ->selectRaw('COUNT(*) as attempts_count, AVG(score_percentage) as average_score, MAX(score_percentage) as best_score')
            ->first();

        return [
            Stat::make('Unlocked courses', $unlockedCourses)
                ->description('Courses ready for practice')
                ->icon('heroicon-o-academic-cap')
                ->color('primary')
                ->url(MyCourses::getUrl(panel: 'student')),
            Stat::make('Completed attempts', (int) ($performance?->attempts_count ?? 0))
                ->description('Submitted and timed-out practices')
                ->icon('heroicon-o-check-circle')
                ->color('info'),
            Stat::make('Average score', $this->percentage($performance?->average_score))
                ->description('Across completed attempts')
                ->icon('heroicon-o-chart-bar')
                ->color('warning'),
            Stat::make('Best score', $this->percentage($performance?->best_score))
                ->description('Your highest result')
                ->icon('heroicon-o-trophy')
                ->color('success'),
        ];
    }

    private function percentage(mixed $value): string
    {
        return $value === null ? '—' : number_format((float) $value, 2).'%';
    }
}
