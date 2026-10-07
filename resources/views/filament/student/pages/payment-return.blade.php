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
    @elseif ($verificationPaused)
        <div class="ef-status-card">
            <div class="ef-status-icon ef-status-icon--warning">!</div>
            <p class="ef-eyebrow">Confirmation delayed</p>
            <h1 class="ef-title">Let’s check that payment again.</h1>
            <p class="ef-subtitle">{{ $verificationNotice }}</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <x-filament::button wire:click="checkAgain" wire:loading.attr="disabled" icon="heroicon-o-arrow-path">Check payment now</x-filament::button>
                <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\PaymentHistory::getUrl(panel: 'student') }}" color="gray">Payment history</x-filament::button>
            </div>
        </div>
    @else
        <div class="ef-status-card" wire:poll.5s="retryVerification">
            <div class="ef-status-icon ef-status-icon--loading" aria-label="Confirming payment"><span></span></div>
            <p class="ef-eyebrow">Confirming secure payment</p>
            <h1 class="ef-title">Verifying your payment…</h1>
            <p class="ef-subtitle">Please keep this page open while we securely confirm your payment with Paystack. Your course will open automatically.</p>
            @if ($verificationNotice)
                <p class="mt-3 text-sm text-gray-500">{{ $verificationNotice }}</p>
            @endif
            <div class="mt-6">
                <x-filament::button tag="a" href="{{ \App\Filament\Student\Pages\AvailableCourses::getUrl(panel: 'student') }}" color="gray">Return to courses</x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
