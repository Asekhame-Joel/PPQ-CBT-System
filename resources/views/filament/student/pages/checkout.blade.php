<x-filament-panels::page>
    <x-student-ui />

    <div class="ef-checkout">
        <header class="ef-page-intro">
            <p class="ef-eyebrow">Unlock course</p>
            <h1 class="ef-title">One step before you practise.</h1>
            <p class="ef-subtitle">Confirm the course below and continue to secure payment.</p>
        </header>

        <section class="ef-checkout-card">
            <div class="ef-checkout-main">
                <span class="ef-code">{{ $course->code }}</span>
                <h2 class="ef-course-name">{{ $course->name }}</h2>
                <p class="ef-description">{{ $course->level?->name ?? 'General course' }}</p>

                <div class="ef-price-row">
                    <span class="ef-meta-label">Amount due</span>
                    <span class="ef-price">&#8358;{{ number_format((float) $course->price, 2) }}</span>
                </div>

                <div class="ef-secure">
                    <span class="ef-secure-mark">✓</span>
                    <p>You will continue to Paystack to complete this payment securely. Your course opens after payment is verified.</p>
                </div>

                <x-filament::button wire:click="pay" wire:loading.attr="disabled" class="w-full" icon="heroicon-o-lock-closed">
                    <span wire:loading.remove wire:target="pay">Continue to secure payment</span>
                    <span wire:loading wire:target="pay">Starting payment…</span>
                </x-filament::button>
            </div>
        </section>
    </div>
</x-filament-panels::page>
