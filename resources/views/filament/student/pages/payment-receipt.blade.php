<x-filament-panels::page>
    <div class="mx-auto max-w-3xl">
        <x-filament::section>
            <div class="space-y-6" id="payment-receipt">
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 pb-5 dark:border-white/10">
                    <div>
                        <p class="text-sm font-semibold text-primary-600 dark:text-primary-400">Exam Practice</p>
                        <h2 class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">Payment receipt</h2>
                    </div>
                    <x-filament::badge color="success">Paid</x-filament::badge>
                </div>

                <dl class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Student</dt>
                        <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $payment->user->name }}</dd>
                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ $payment->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Course</dt>
                        <dd class="mt-1 font-semibold text-gray-950 dark:text-white">
                            {{ $payment->course->code }} — {{ $payment->course->name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Reference</dt>
                        <dd class="mt-1 font-mono text-sm font-semibold text-gray-950 dark:text-white">{{ $payment->reference }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Payment date</dt>
                        <dd class="mt-1 font-semibold text-gray-950 dark:text-white">
                            {{ $payment->paid_at->format('M j, Y g:i A') }}
                        </dd>
                    </div>
                </dl>

                <div class="flex items-center justify-between rounded-xl bg-gray-50 p-5 dark:bg-white/5">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Total paid</span>
                    <span class="text-2xl font-bold text-gray-950 dark:text-white">
                        &#8358;{{ number_format((float) $payment->amount, 2) }}
                    </span>
                </div>

                <div class="print:hidden">
                    <x-filament::button type="button" onclick="window.print()" icon="heroicon-o-printer">
                        Print receipt
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
