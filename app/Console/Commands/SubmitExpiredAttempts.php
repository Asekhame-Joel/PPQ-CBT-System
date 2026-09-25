<?php

namespace App\Console\Commands;

use App\Enums\AttemptStatus;
use App\Models\QuizAttempt;
use App\Practice\SubmitPracticeAttempt;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SubmitExpiredAttempts extends Command
{
    protected $signature = 'exam:submit-expired-attempts';

    protected $description = 'Submit and score practice attempts that expired while a student was offline';

    public function handle(SubmitPracticeAttempt $submitAttempt): int
    {
        $processed = 0;

        QuizAttempt::query()
            ->with('user')
            ->where('status', AttemptStatus::InProgress)
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function (Collection $attempts) use ($submitAttempt, &$processed): void {
                foreach ($attempts as $attempt) {
                    $submitAttempt->handle($attempt->user, $attempt->id, requireExpired: true);
                    $processed++;
                }
            });

        $this->info("Submitted {$processed} expired practice attempt(s).");

        return self::SUCCESS;
    }
}
