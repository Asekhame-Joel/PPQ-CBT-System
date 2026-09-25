<x-filament-widgets::widget>
    <x-student-ui />

    <div class="ef-dashboard-hero">
        <div>
            <p class="ef-eyebrow">Ready when you are</p>
            <h2 class="ef-course-name">Practise a past-question course today.</h2>
            <p class="ef-description">Unlock a course or continue one you already own.</p>
        </div>
        <div class="ef-dashboard-actions">
            <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\MyCourses::getUrl(panel: 'student') }}" icon="heroicon-o-play">My courses</x-filament::button>
            <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\AvailableCourses::getUrl(panel: 'student') }}" color="gray" icon="heroicon-o-book-open">Browse courses</x-filament::button>
        </div>
    </div>

    <div class="ef-dashboard-activity grid gap-6 xl:grid-cols-2">
        <x-filament::section heading="Continue practicing" icon="heroicon-o-play-circle">
            <div class="space-y-3">
                @forelse ($this->activeAttempts as $attempt)
                    <a
                        href="{{ \App\Filament\Student\Pages\PracticeSession::getUrl(['attempt' => $attempt], panel: 'student') }}"
                        class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 p-4 transition hover:border-primary-400 dark:border-white/10"
                    >
                        <div>
                            <p class="font-semibold text-gray-950 dark:text-white">
                                {{ $attempt->course->code }} · {{ $attempt->course->name }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $attempt->answered_count }} of {{ $attempt->question_count }} answered
                            </p>
                        </div>
                        <x-filament::badge color="warning">
                            Ends {{ $attempt->expires_at->diffForHumans() }}
                        </x-filament::badge>
                    </a>
                @empty
                    <p class="text-sm text-gray-600 dark:text-gray-400">You have no active practice attempts.</p>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::section heading="Recent results" icon="heroicon-o-clock">
            <div class="space-y-3">
                @forelse ($this->recentAttempts as $attempt)
                    <a
                        href="{{ \App\Filament\Student\Pages\PracticeResult::getUrl(['attempt' => $attempt], panel: 'student') }}"
                        class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 p-4 transition hover:border-primary-400 dark:border-white/10"
                    >
                        <div>
                            <p class="font-semibold text-gray-950 dark:text-white">
                                {{ $attempt->course->code }} · {{ $attempt->course->name }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $attempt->submitted_at->toFormattedDateString() }}
                            </p>
                        </div>
                        <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                            {{ number_format((float) $attempt->score_percentage, 2) }}%
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-gray-600 dark:text-gray-400">Your completed practices will appear here.</p>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
