<?php

namespace App\Filament\Widgets;

use App\Enums\AttemptStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Courses\CourseResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\Students\StudentResource;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeStudents = User::query()
            ->where('role', UserRole::Student)
            ->where('is_active', true)
            ->count();
        $activeCourses = Course::query()->active()->count();
        $activeQuestions = Question::query()->active()->count();
        $monthlyRevenue = Payment::query()
            ->successful()
            ->where('paid_at', '>=', now()->startOfMonth())
            ->sum('amount');
        $practiceToday = QuizAttempt::query()
            ->where('started_at', '>=', today())
            ->count();
        $completedToday = QuizAttempt::query()
            ->whereIn('status', [AttemptStatus::Submitted->value, AttemptStatus::Expired->value])
            ->where('submitted_at', '>=', today())
            ->count();

        return [
            Stat::make('Active students', number_format($activeStudents))
                ->description('Students able to sign in')
                ->icon('heroicon-o-users')
                ->color('primary')
                ->url(StudentResource::getUrl('index')),
            Stat::make('Active courses', number_format($activeCourses))
                ->description(number_format($activeQuestions).' active questions')
                ->icon('heroicon-o-academic-cap')
                ->color('info')
                ->url(CourseResource::getUrl('index')),
            Stat::make('Revenue this month', '₦'.number_format((float) $monthlyRevenue, 2))
                ->description('Verified successful payments')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->url(PaymentResource::getUrl('index')),
            Stat::make('Practice today', number_format($practiceToday))
                ->description(number_format($completedToday).' completed today')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->url(QuestionResource::getUrl('index')),
        ];
    }
}
