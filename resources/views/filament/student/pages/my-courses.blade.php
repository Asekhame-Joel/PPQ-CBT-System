<x-filament-panels::page>
    <x-student-ui />

    <header class="ef-page-intro">
        <p class="ef-eyebrow">Your library</p>
        <h1 class="ef-title">My Courses</h1>
        <p class="ef-subtitle">Open a course, choose your questions and begin your next practice.</p>
    </header>

    <div class="ef-grid">
        @forelse ($this->courseAccesses as $access)
            @php
                $course = $access->course;
                $canPractice = $course->questions_count >= $course->min_question_count;
            @endphp

            <article class="ef-card">
                <div class="ef-card-top">
                    <span class="ef-code">{{ $course->code }}</span>
                    <span class="ef-badge ef-badge--ok">✓ Unlocked</span>
                </div>

                <h2 class="ef-course-name">{{ $course->name }}</h2>
                <p class="ef-description">{{ filled($course->description) ? $course->description : 'Your unlocked past-question practice course.' }}</p>
                <div class="ef-details">
                    @if ($course->level)<span>{{ $course->level->name }}</span>@endif
                    <span>{{ number_format($course->questions_count) }} questions</span>
                    <span>{{ $access->expires_at ? 'Until '.$access->expires_at->toFormattedDateString() : 'No expiry' }}</span>
                </div>

                <div class="ef-card-footer">
                    <div>
                        <p class="ef-meta-label">Access</p>
                        <p class="ef-meta-value">Ready to practise</p>
                    </div>

                    @if ($canPractice)
                        <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\PracticeSetup::getUrl(['course' => $course], panel: 'student') }}" icon="heroicon-o-adjustments-horizontal">Set up practice</x-filament::button>
                    @else
                        <x-filament::button disabled color="gray" icon="heroicon-o-clock">Questions not available yet</x-filament::button>
                    @endif
                </div>
            </article>
        @empty
            <div class="ef-empty md:col-span-2 xl:col-span-3">
                <h2 class="ef-course-name">No unlocked courses yet</h2>
                <p class="ef-description">Courses you unlock will appear here when access is active.</p>
                <div class="mt-5">
                    <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\AvailableCourses::getUrl(panel: 'student') }}" icon="heroicon-o-book-open">Browse available courses</x-filament::button>
                </div>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
