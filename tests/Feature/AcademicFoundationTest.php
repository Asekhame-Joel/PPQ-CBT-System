<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Level;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AcademicFoundationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_has_department_and_level_relationships(): void
    {
        $department = Department::factory()->create();
        $level = Level::factory()->create();
        $student = User::factory()
            ->for($department)
            ->for($level)
            ->create();

        $this->assertTrue($student->department->is($department));
        $this->assertTrue($student->level->is($level));
        $this->assertTrue($department->users->contains($student));
        $this->assertTrue($level->users->contains($student));
        $this->assertSame(UserRole::Student, $student->role);
        $this->assertTrue($student->is_active);
    }

    public function test_admin_does_not_require_academic_assignments(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertNull($admin->department);
        $this->assertNull($admin->level);
    }

    public function test_active_scopes_exclude_inactive_academic_records(): void
    {
        $activeDepartment = Department::factory()->create();
        Department::factory()->inactive()->create();
        $activeLevel = Level::factory()->create();
        Level::factory()->inactive()->create();

        $this->assertTrue(Department::active()->sole()->is($activeDepartment));
        $this->assertTrue(Level::active()->sole()->is($activeLevel));
    }
}
