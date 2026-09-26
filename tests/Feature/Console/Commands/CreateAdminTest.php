<?php

namespace Tests\Feature\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_command_creates_active_admin_without_academic_assignments(): void
    {
        $this->artisan('exam:create-admin', [
            '--name' => 'System Administrator',
            '--email' => 'ADMIN@example.com',
            '--phone' => '08012345678',
        ])
            ->expectsQuestion('Password', 'password123')
            ->expectsQuestion('Confirm password', 'password123')
            ->expectsOutputToContain('Administrator [admin@example.com] created successfully.')
            ->assertExitCode(Command::SUCCESS);

        $admin = User::where('email', 'admin@example.com')->sole();

        $this->assertSame('System Administrator', $admin->name);
        $this->assertSame('08012345678', $admin->phone);
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertNull($admin->department_id);
        $this->assertNull($admin->level_id);
        $this->assertTrue(Hash::check('password123', $admin->password));
    }

    public function test_command_rejects_duplicate_email(): void
    {
        User::factory()->admin()->create(['email' => 'admin@example.com']);

        $this->artisan('exam:create-admin', [
            '--name' => 'Second Administrator',
            '--email' => 'admin@example.com',
        ])
            ->expectsQuestion('Password', 'password123')
            ->expectsQuestion('Confirm password', 'password123')
            ->expectsOutputToContain('The email has already been taken.')
            ->assertExitCode(Command::FAILURE);

        $this->assertSame(1, User::where('email', 'admin@example.com')->count());
    }

    public function test_command_can_use_a_production_secret_without_interaction(): void
    {
        putenv('EXAM_ADMIN_PASSWORD=cloud-admin-password');

        try {
            $this->artisan('exam:create-admin', [
                '--name' => 'Cloud Administrator',
                '--email' => 'cloud.admin@example.com',
            ])
                ->expectsOutputToContain('Administrator [cloud.admin@example.com] created successfully.')
                ->assertExitCode(Command::SUCCESS);
        } finally {
            putenv('EXAM_ADMIN_PASSWORD');
        }

        $admin = User::where('email', 'cloud.admin@example.com')->sole();
        $this->assertTrue(Hash::check('cloud-admin-password', $admin->password));
    }

    public function test_command_rejects_mismatched_password_confirmation(): void
    {
        $this->artisan('exam:create-admin', [
            '--name' => 'System Administrator',
            '--email' => 'admin@example.com',
        ])
            ->expectsQuestion('Password', 'password123')
            ->expectsQuestion('Confirm password', 'different-password')
            ->expectsOutputToContain('The password field confirmation does not match.')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }
}
