<x-filament-panels::page>
    <x-student-ui />

    @if ($successful)
        <div
            class="ef-status-card"
            x-data
            x-init="window.setTimeout(() => window.location.assign('{{ \App\Filament\Student\Pages\PracticeSetup::getUrl(['course' => $payment->course], panel: 'student') }}'), 1000)"
        >
            <div class="ef-status-icon ef-status-icon--success">✓</div>
            <p class="ef-eyebrow">Payment complete</p>
            <h1 class="ef-title">Payment confirmed</h1>
            <p class="ef-subtitle">{{ $payment->course->code }} has been unlocked. Opening your course now…</p>
            <div class="mt-6">
                <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\PracticeSetup::getUrl(['course' => $payment->course], panel: 'student') }}" icon="heroicon-o-play">Open course now</x-filament::button>
            </div>
        </div>
    @else
        <div class="ef-status-card" wire:init="retryVerification" wire:poll.2s="retryVerification">
            <div class="ef-status-icon ef-status-icon--loading" aria-label="Confirming payment"><span></span></div>
            <p class="ef-eyebrow">Confirming secure payment</p>
            <h1 class="ef-title">Verifying your payment…</h1>
            <p class="ef-subtitle">Please keep this page open while we securely confirm your payment with Paystack. Your course will open automatically.</p>
            <div class="mt-6">
                <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\AvailableCourses::getUrl(panel: 'student') }}" color="gray">Return to courses</x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
