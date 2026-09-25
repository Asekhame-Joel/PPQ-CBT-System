<x-filament-panels::page>
    <x-student-ui />

    <div class="ef-receipt" id="payment-receipt">
        <div class="ef-receipt-main">
            <div class="ef-receipt-head">
                <div>
                    <p class="ef-eyebrow">Exam Practice</p>
                    <h1 class="ef-title">Payment receipt</h1>
                </div>
                <span class="ef-badge ef-badge--ok">✓ Paid</span>
            </div>

            <dl class="ef-receipt-grid">
                <div>
                    <dt class="ef-meta-label">Student</dt>
                    <dd class="ef-meta-value">{{ $payment->user->name }}</dd>
                    <dd class="ef-description">{{ $payment->user->email }}</dd>
                </div>
                <div>
                    <dt class="ef-meta-label">Course</dt>
                    <dd class="ef-meta-value">{{ $payment->course->code }}</dd>
                    <dd class="ef-description">{{ $payment->course->name }}</dd>
                </div>
                <div>
                    <dt class="ef-meta-label">Reference</dt>
                    <dd class="ef-meta-value font-mono text-sm">{{ $payment->reference }}</dd>
                </div>
                <div>
                    <dt class="ef-meta-label">Payment date</dt>
                    <dd class="ef-meta-value text-base">{{ $payment->paid_at->format('M j, Y g:i A') }}</dd>
                </div>
            </dl>

            <div class="ef-receipt-total">
                <span class="ef-meta-label">Total paid</span>
                <span class="ef-price">&#8358;{{ number_format((float) $payment->amount, 2) }}</span>
            </div>

            <div class="mt-6 print:hidden">
                <x-filament::button type="button" onclick="window.print()" icon="heroicon-o-printer">Print receipt</x-filament::button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
