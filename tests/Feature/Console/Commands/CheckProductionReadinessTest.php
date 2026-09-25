<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class CheckProductionReadinessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_command_passes_when_required_production_services_are_configured(): void
    {
        config()->set([
            'app.env' => 'production',
            'app.debug' => false,
            'app.key' => 'base64:production-key',
            'app.url' => 'https://examforge.example',
            'app.maintenance.driver' => 'cache',
            'queue.default' => 'redis',
            'cache.default' => 'redis',
            'session.driver' => 'redis',
            'queue.failed.driver' => 'database-uuids',
            'services.paystack.secret_key' => 'secret-key',
        ]);
        $connection = Mockery::mock();
        $connection->shouldReceive('ping')->once()->andReturn(true);
        Redis::shouldReceive('connection')->once()->andReturn($connection);

        $this->artisan('exam:check-production')
            ->expectsOutputToContain('PASS  Database connection')
            ->expectsOutputToContain('PASS  Redis connection')
            ->expectsOutputToContain('ExamForge production checks passed.')
            ->assertExitCode(Command::SUCCESS);
    }

    public function test_command_fails_when_critical_production_configuration_is_missing(): void
    {
        config()->set([
            'app.env' => 'local',
            'app.debug' => true,
            'app.key' => null,
            'app.url' => 'http://localhost',
            'app.maintenance.driver' => 'file',
            'queue.default' => 'sync',
            'cache.default' => 'array',
            'session.driver' => 'file',
            'queue.failed.driver' => 'null',
            'services.paystack.secret_key' => null,
        ]);
        $connection = Mockery::mock();
        $connection->shouldReceive('ping')->once()->andReturn(false);
        Redis::shouldReceive('connection')->once()->andReturn($connection);

        $this->artisan('exam:check-production')
            ->expectsOutputToContain('FAIL  Production environment')
            ->expectsOutputToContain('FAIL  Application encryption key')
            ->expectsOutputToContain('FAIL  Paystack secret key')
            ->expectsOutputToContain('ExamForge is not ready for production.')
            ->assertExitCode(Command::FAILURE);
    }
}
