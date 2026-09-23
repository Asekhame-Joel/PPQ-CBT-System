<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Departments\Pages\CreateDepartment;
use App\Filament\Resources\Departments\Pages\EditDepartment;
use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Models\Department;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DepartmentResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_list_departments(): void
    {
        $admin = User::factory()->admin()->create();
        $departments = Department::factory()->count(3)->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListDepartments::class)
            ->assertCanSeeTableRecords($departments);
    }

    public function test_admin_can_create_department(): void
    {
        $admin = User::factory()->admin()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateDepartment::class)
            ->fillForm([
                'name' => 'Computer Science',
                'code' => 'CSC',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('departments', [
            'name' => 'Computer Science',
            'code' => 'CSC',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_department_without_triggering_unique_rules(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create([
            'name' => 'Computer Science',
            'code' => 'CSC',
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(EditDepartment::class, ['record' => $department->id])
            ->fillForm([
                'name' => 'Computer Science',
                'code' => 'CSC',
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_create_duplicate_department_name_or_code(): void
    {
        $admin = User::factory()->admin()->create();
        Department::factory()->create([
            'name' => 'Computer Science',
            'code' => 'CSC',
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateDepartment::class)
            ->fillForm([
                'name' => 'Computer Science',
                'code' => 'CSC',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'unique',
                'code' => 'unique',
            ]);

        $this->assertSame(1, Department::where('name', 'Computer Science')->count());
        $this->assertSame(1, Department::where('code', 'CSC')->count());
    }
}
