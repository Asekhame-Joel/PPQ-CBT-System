<?php

namespace App\Filament\Student\Pages;

use App\Filament\Student\Widgets\StudentAttemptActivity;
use App\Filament\Student\Widgets\StudentStatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public function getSubheading(): string|Htmlable|null
    {
        return 'Choose a course, continue an active practice, or review your latest result.';
    }

    public function getWidgets(): array
    {
        return [
            StudentStatsOverview::class,
            StudentAttemptActivity::class,
        ];
    }
}
