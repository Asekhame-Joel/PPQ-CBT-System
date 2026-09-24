<?php

namespace App\Filament\Student\Pages;

use App\Filament\Student\Widgets\StudentAttemptActivity;
use App\Filament\Student\Widgets\StudentStatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StudentStatsOverview::class,
            StudentAttemptActivity::class,
        ];
    }
}
