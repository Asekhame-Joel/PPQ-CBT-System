<?php

namespace App\Filament\Student\Pages;

use App\Models\Course;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

class AvailableCourses extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.student.pages.available-courses';

    /** @return Collection<int, Course> */
    #[Computed]
    public function courses(): Collection
    {
        /** @var User $student */
        $student = auth()->user();

        return Course::query()
            ->active()
            ->eligibleFor($student)
            ->with('level')
            ->withCount(['questions' => fn ($query) => $query->active()])
            ->withExists(['courseAccesses as is_unlocked' => fn ($query) => $query
                ->available()
                ->where('user_id', $student->getKey())])
            ->orderBy('code')
            ->get();
    }

    public function hasCompleteAcademicProfile(): bool
    {
        return filled(auth()->user()?->level_id) && filled(auth()->user()?->department_id);
    }
}
