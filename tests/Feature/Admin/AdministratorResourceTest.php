<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdministratorResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_create_and_manage_another_administrator(): void
    {
        $admin = User::factory()->admin()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'ExamForge Manager',
                'email' => 'manager@examforge.test',
                'phone' => '08012345678',
                'is_active' => true,
                'password' => 'secure-password',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $administrator = User::query()->where('email', 'manager@examforge.test')->sole();
        $this->assertSame(UserRole::Admin, $administrator->role);
        $this->assertTrue($administrator->is_active);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertCanSeeTableRecords([$admin, $administrator]);
    }

    public function test_student_cannot_open_administrator_management(): void
    {
        $student = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs($student)
            ->get(ListUsers::getUrl(panel: 'admin'))
            ->assertForbidden();
    }
}
