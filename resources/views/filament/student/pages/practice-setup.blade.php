<x-filament-panels::page>
    <x-filament::section>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-primary-600 dark:text-primary-400">
                    {{ $course->code }}
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-950 dark:text-white">
                    {{ $course->name }}
                </h2>
            </div>

            <x-filament::badge color="success" icon="heroicon-o-check-circle">
                {{ number_format($availableQuestionCount) }} questions available
            </x-filament::badge>
        </div>
    </x-filament::section>

    <form wire:submit="startPractice" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
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
</x-filament-panels::page>
