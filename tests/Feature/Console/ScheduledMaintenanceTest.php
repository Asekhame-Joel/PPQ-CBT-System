<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class ScheduledMaintenanceTest extends TestCase
{
    public function test_old_failed_queue_jobs_are_scheduled_for_daily_pruning(): void
    {
        $commands = collect(app(Schedule::class)->events())
            ->map(fn ($event): string => (string) $event->command);

        $this->assertTrue(
            $commands->contains(fn (string $command): bool => str_contains($command, 'queue:prune-failed --hours=168')),
        );
    }
}
