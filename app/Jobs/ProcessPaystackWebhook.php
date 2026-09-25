<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Payments\ConfirmCoursePayment;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPaystackWebhook implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $uniqueFor = 300;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60, 120];

    public function __construct(public readonly string $reference) {}

    public function handle(ConfirmCoursePayment $confirmPayment): void
    {
        $payment = Payment::query()->where('reference', $this->reference)->first();

        if ($payment) {
            $confirmPayment->handle($payment);
        }
    }

    public function uniqueId(): string
    {
        return $this->reference;
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Paystack webhook processing failed.', [
            'reference' => $this->reference,
            'exception' => $exception::class,
        ]);
    }
}
