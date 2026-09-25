<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class RevenueTrend extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Verified revenue — last 7 days';

    protected ?string $description = 'Successful Paystack payments in naira.';

    protected function getData(): array
    {
        $start = CarbonImmutable::today()->subDays(6);
        $payments = Payment::query()
            ->successful()
            ->whereBetween('paid_at', [$start, CarbonImmutable::today()->endOfDay()])
            ->get(['amount', 'paid_at'])
            ->groupBy(fn (Payment $payment): string => $payment->paid_at->toDateString())
            ->map(fn ($payments): float => (float) $payments->sum('amount'));

        $labels = [];
        $values = [];

        foreach (range(0, 6) as $offset) {
            $day = $start->addDays($offset);
            $labels[] = $day->format('D, M j');
            $values[] = $payments->get($day->toDateString(), 0.0);
        }

        return [
            'datasets' => [[
                'label' => 'Revenue (NGN)',
                'data' => $values,
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
