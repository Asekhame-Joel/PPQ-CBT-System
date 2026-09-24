<?php

namespace App\Filament\Student\Pages;

use App\Models\CourseAccess;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

class MyCourses extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.student.pages.my-courses';

    /** @return Collection<int, CourseAccess> */
    #[Computed]
    public function courseAccesses(): Collection
    {
        return CourseAccess::query()
            ->available()
            ->where('user_id', auth()->id())
            ->whereHas('course', fn ($query) => $query->active())
            ->with([
                'course' => fn ($query) => $query
                    ->with('level')
                    ->withCount(['questions' => fn ($query) => $query->active()]),
            ])
            ->latest('granted_at')
            ->get();
    }
}
