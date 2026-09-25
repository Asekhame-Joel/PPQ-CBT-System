<x-filament-panels::page>
    <x-student-ui />

    <div class="ef-status-card">
        @if ($successful)
            <div class="ef-status-icon ef-status-icon--success">✓</div>
            <p class="ef-eyebrow">Payment complete</p>
            <h1 class="ef-title">Payment successful</h1>
            <p class="ef-subtitle">{{ $payment->course->code }} has been unlocked and is ready for practice.</p>
            <div class="mt-6">
                <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\PracticeSetup::getUrl(['course' => $payment->course], panel: 'student') }}" icon="heroicon-o-play">Start practice</x-filament::button>
            </div>
        @else
            <div class="ef-status-icon ef-status-icon--pending">!</div>
            <p class="ef-eyebrow">Verification pending</p>
            <h1 class="ef-title">Payment not confirmed</h1>
            <p class="ef-subtitle">If you were charged, please wait briefly. An administrator can also confirm a pending payment after checking it.</p>
            <div class="mt-6">
                <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\AvailableCourses::getUrl(panel: 'student') }}" color="gray">Return to courses</x-filament::button>
            </div>
        @endif
    </div>
</x-filament-panels::page>
