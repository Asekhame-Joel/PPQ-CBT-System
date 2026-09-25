<x-filament-panels::page>
    <x-student-ui />

    <header class="ef-page-intro">
        <p class="ef-eyebrow">Course catalogue</p>
        <h1 class="ef-title">Choose a course and start practising.</h1>
        <p class="ef-subtitle">Past questions prepared for your department and level.</p>
    </header>

    <div class="ef-grid">
        @forelse ($this->courses as $course)
            @php($hasEnoughQuestions = $course->questions_count >= $course->min_question_count)

            <article class="ef-card">
                <div class="ef-card-top">
                    <span class="ef-code">{{ $course->code }}</span>
                    <span @class(['ef-badge', 'ef-badge--ok' => $course->is_unlocked, 'ef-badge--locked' => ! $course->is_unlocked])>
                        {{ $course->is_unlocked ? '✓ Unlocked' : 'Locked' }}
                    </span>
                </div>

                <h2 class="ef-course-name">{{ $course->name }}</h2>
                <p class="ef-description">
                    {{ filled($course->description) ? $course->description : number_format($course->questions_count).' past questions ready for practice.' }}
                </p>
                <div class="ef-details">
                    <span>{{ $course->level?->name ?? 'General' }}</span>
                    <span>{{ number_format($course->questions_count) }} questions</span>
                </div>

                <div class="ef-card-footer">
                    <div>
                        <p class="ef-meta-label">Course access</p>
                        <p class="ef-meta-value">&#8358;{{ number_format((float) $course->price, 2) }}</p>
                    </div>

                    @if ($course->is_unlocked && $hasEnoughQuestions)
                        <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\PracticeSetup::getUrl(['course' => $course], panel: 'student') }}" icon="heroicon-o-play">Start practice</x-filament::button>
                    @elseif ($course->is_unlocked)
                        <x-filament::button disabled color="gray" icon="heroicon-o-clock">Questions not available yet</x-filament::button>
                    @elseif ($hasEnoughQuestions)
                        <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\Checkout::getUrl(['course' => $course], panel: 'student') }}" icon="heroicon-o-lock-closed">Unlock course</x-filament::button>
                    @else
                        <x-filament::button disabled color="gray" icon="heroicon-o-clock">Not available yet</x-filament::button>
                    @endif
                </div>
            </article>
        @empty
            <div class="ef-empty md:col-span-2 xl:col-span-3">
                @if ($this->hasCompleteAcademicProfile())
                    <h2 class="ef-course-name">No courses available</h2>
                    <p class="ef-description">There are no active courses for your level and department yet.</p>
                @else
                    <h2 class="ef-course-name">Complete your academic profile</h2>
                    <p class="ef-description">Your department and level are required before we can show your available courses.</p>
                @endif
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
