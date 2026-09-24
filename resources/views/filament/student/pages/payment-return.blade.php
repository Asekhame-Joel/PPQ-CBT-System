<x-filament-panels::page>
    <div class="mx-auto max-w-2xl">
        @if ($successful)
            <x-filament::section icon="heroicon-o-check-circle" heading="Payment successful">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $payment->course->code }} has been unlocked and is ready for practice.
                </p>

                <div class="mt-5">
                    <x-filament::button
                        tag="a"
                        href="{{ \App\Filament\Student\Pages\PracticeSetup::getUrl(['course' => $payment->course], panel: 'student') }}"
                        icon="heroicon-o-play"
                    >
                        Start practice
                    </x-filament::button>
                </div>
            </x-filament::section>
        @else
            <x-filament::section icon="heroicon-o-clock" heading="Payment not confirmed">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    We could not confirm this payment yet. If you were charged, please wait briefly and return to this page.
                </p>

                <div class="mt-5">
                    <x-filament::button
                        tag="a"
                        href="{{ \App\Filament\Student\Pages\AvailableCourses::getUrl(panel: 'student') }}"
                        color="gray"
                    >
                        Return to courses
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
