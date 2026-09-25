<x-filament-panels::page>
    <x-student-ui />

    <header class="ef-page-intro">
        <p class="ef-eyebrow">Your payments</p>
        <h1 class="ef-title">Payment history</h1>
        <p class="ef-subtitle">Check payment status and open receipts for completed course purchases.</p>
    </header>

    {{ $this->table }}
</x-filament-panels::page>
