<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Student\Pages\Auth\Register;
use App\Models\Department;
use App\Models\Level;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_registration_page_is_public_but_admin_registration_is_unavailable(): void
    {
        $this->get('/student/register')->assertSuccessful();
        $this->get('/admin/register')->assertNotFound();
    }

    public function test_valid_registration_creates_active_student_with_academic_assignments(): void
    {
        $department = Department::factory()->create();
        $level = Level::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Ada Student',
                'email' => 'ada@example.com',
                'phone' => '08012345678',
                'department_id' => $department->id,
                'level_id' => $level->id,
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ])
            ->set('data.role', 'admin')
            ->set('data.is_active', false)
            ->call('register')
            ->assertHasNoFormErrors()
            ->assertRedirect('/student');

        $student = User::where('email', 'ada@example.com')->sole();

        $this->assertSame('Ada Student', $student->name);
        $this->assertSame('08012345678', $student->phone);
        $this->assertSame(UserRole::Student, $student->role);
        $this->assertTrue($student->is_active);
        $this->assertTrue($student->department->is($department));
        $this->assertTrue($student->level->is($level));
        $this->assertTrue(Hash::check('password123', $student->password));
        $this->assertAuthenticatedAs($student);
    }

    public function test_registration_rejects_inactive_academic_assignments(): void
    {
        $department = Department::factory()->inactive()->create();
        $level = Level::factory()->inactive()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Ada Student',
                'email' => 'inactive-options@example.com',
                'phone' => '08012345678',
                'department_id' => $department->id,
                'level_id' => $level->id,
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ])
            ->call('register')
            ->assertHasFormErrors([
                'department_id' => 'in',
                'level_id' => 'in',
            ]);

        $this->assertDatabaseMissing('users', ['email' => 'inactive-options@example.com']);
    }

    public function test_registration_requires_identity_academic_and_password_fields(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::test(Register::class)
            ->fillForm([
                'name' => null,
                'email' => null,
                'phone' => null,
                'department_id' => null,
                'level_id' => null,
                'password' => null,
                'passwordConfirmation' => null,
            ])
            ->call('register')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'department_id' => 'required',
                'level_id' => 'required',
                'password' => 'required',
                'passwordConfirmation' => 'required',
            ]);
    }
}
