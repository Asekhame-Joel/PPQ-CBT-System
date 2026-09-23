<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_each_panel_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/student')->assertRedirect('/student/login');
    }

    public function test_active_admin_can_only_access_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertSuccessful();
        $this->actingAs($admin)->get('/student')->assertForbidden();
    }

    public function test_active_student_can_only_access_student_panel(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/student')->assertSuccessful();
        $this->actingAs($student)->get('/admin')->assertForbidden();
    }

    public function test_inactive_users_cannot_access_panels(): void
    {
        $inactiveAdmin = User::factory()->admin()->inactive()->create();
        $inactiveStudent = User::factory()->inactive()->create();

        $this->actingAs($inactiveAdmin)->get('/admin')->assertForbidden();
        $this->actingAs($inactiveStudent)->get('/student')->assertForbidden();
    }

    public function test_unknown_panels_are_denied_by_default(): void
    {
        $admin = User::factory()->admin()->create();
        $unknownPanel = Panel::make()->id('unknown');

        $this->assertFalse($admin->canAccessPanel($unknownPanel));
    }
}
