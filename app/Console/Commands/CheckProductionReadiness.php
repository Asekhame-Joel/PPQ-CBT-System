<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class CheckProductionReadiness extends Command
{
    protected $signature = 'exam:check-production';

    protected $description = 'Check whether ExamForge production services are configured';

    public function handle(): int
    {
        $checks = [
            'Production environment' => config('app.env') === 'production',
            'Debug mode disabled' => config('app.debug') === false,
            'Application encryption key' => filled(config('app.key')),
            'HTTPS application URL' => str_starts_with((string) config('app.url'), 'https://'),
            'Database connection' => $this->databaseIsAvailable(),
            'Redis connection' => $this->redisIsAvailable(),
            'Redis queue' => config('queue.default') === 'redis',
            'Redis cache' => config('cache.default') === 'redis',
            'Redis sessions' => config('session.driver') === 'redis',
            'Shared maintenance mode' => config('app.maintenance.driver') === 'cache',
            'Failed queue job storage' => config('queue.failed.driver') === 'database-uuids',
            'Paystack secret key' => filled(config('services.paystack.secret_key')),
        ];

        foreach ($checks as $label => $passed) {
            $this->line(($passed ? 'PASS  ' : 'FAIL  ').$label);
        }

        if (in_array(false, $checks, true)) {
            $this->newLine();
            $this->error('ExamForge is not ready for production. Resolve the failed checks above.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('ExamForge production checks passed.');

        return self::SUCCESS;
    }

    private function databaseIsAvailable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function redisIsAvailable(): bool
    {
        try {
            return Redis::connection()->ping() !== false;
        } catch (Throwable) {
            return false;
        }
    }
}
