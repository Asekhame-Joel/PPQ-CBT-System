<x-filament-panels::page>
    <div class="mx-auto max-w-2xl">
        <x-filament::section heading="Confirm your course">
            <div class="space-y-5">
                <div>
                    <p class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $course->code }}</p>
                    <h2 class="mt-1 text-xl font-bold text-gray-950 dark:text-white">{{ $course->name }}</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $course->level?->name }}</p>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Amount due</span>
                    <span class="text-xl font-bold text-gray-950 dark:text-white">
                        &#8358;{{ number_format((float) $course->price, 2) }}
                    </span>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    You will continue to Paystack to complete this payment securely. Course access is granted only after the payment is verified.
                </p>

                <x-filament::button wire:click="pay" wire:loading.attr="disabled" class="w-full" icon="heroicon-o-lock-closed">
                    <span wire:loading.remove wire:target="pay">Continue to secure payment</span>
                    <span wire:loading wire:target="pay">Starting payment…</span>
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
