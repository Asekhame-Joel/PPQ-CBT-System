<x-filament-panels::page>
    <x-student-ui />

    <div class="ef-setup-shell">
    <header class="ef-page-intro">
        <p class="ef-eyebrow">Practice setup</p>
        <h1 class="ef-title">Prepare your session.</h1>
        <p class="ef-subtitle">Choose a comfortable question count and duration before you begin.</p>
    </header>

    <section class="ef-setup-course">
        <div class="ef-setup-course-mark">{{ mb_substr($course->code, 0, 2) }}</div>
        <div class="ef-setup-course-copy">
            <span>{{ $course->code }}</span>
            <strong>{{ $course->name }}</strong>
            <small>This course is unlocked and ready for practice.</small>
        </div>
        <div class="ef-setup-available"><strong>{{ number_format($availableQuestionCount) }}</strong><span>{{ number_format($availableQuestionCount) }} questions available</span></div>
    </section>

    <form wire:submit="startPractice" class="ef-setup-form">
        {{ $this->form }}

        <div class="ef-setup-actions">
            <x-filament::button type="submit" icon="heroicon-o-play" wire:loading.attr="disabled">
                Start practice
            </x-filament::button>

            <x-filament::button type="button" wire:click="saveSettings" color="gray" icon="heroicon-o-check">
                Save settings
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Student\Pages\MyCourses::getUrl(panel: 'student') }}"
                color="gray"
            >
                Back to my courses
            </x-filament::button>
        </div>
    </form>
    </div>
</x-filament-panels::page>
