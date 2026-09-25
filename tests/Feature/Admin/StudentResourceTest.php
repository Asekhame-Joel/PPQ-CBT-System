<?php

namespace Tests\Feature\Admin;

use App\Enums\AccessSource;
use App\Enums\UserRole;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\RelationManagers\CourseAccessesRelationManager;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Department;
use App\Models\Level;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_sees_students_but_not_administrator_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        $students = User::factory()->count(2)->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListStudents::class)
            ->assertCanSeeTableRecords($students)
            ->assertCanNotSeeTableRecords([$admin]);
    }

    public function test_admin_can_create_a_student_with_an_academic_profile(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create();
        $level = Level::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateStudent::class)
            ->fillForm([
                'name' => 'New Student',
                'email' => 'new.student@example.com',
                'phone' => '08012345678',
                'department_id' => $department->id,
                'level_id' => $level->id,
                'is_active' => true,
                'password' => 'secure-password',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $student = User::query()->where('email', 'new.student@example.com')->sole();
        $this->assertSame(UserRole::Student, $student->role);
        $this->assertSame($department->id, $student->department_id);
        $this->assertSame($level->id, $student->level_id);
    }

    public function test_admin_can_update_student_profile_without_changing_password(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $newLevel = Level::factory()->create();
        $originalPassword = $student->password;
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(EditStudent::class, ['record' => $student->id])
            ->fillForm([
                'name' => 'Updated Student',
                'level_id' => $newLevel->id,
                'password' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $student->refresh();
        $this->assertSame('Updated Student', $student->name);
        $this->assertSame($newLevel->id, $student->level_id);
        $this->assertSame($originalPassword, $student->password);
    }

    public function test_admin_can_grant_and_revoke_manual_course_access(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create(['level_id' => $student->level_id]);
        $course->departments()->attach($student->department_id);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $component = Livewire::actingAs($admin)->test(CourseAccessesRelationManager::class, [
            'ownerRecord' => $student,
            'pageClass' => EditStudent::class,
        ]);
        $component
            ->callAction(TestAction::make('grantCourse')->table(), [
                'course_id' => $course->id,
                'expires_at' => now()->addMonth()->toDateTimeString(),
            ])
            ->assertHasNoActionErrors();

        $access = CourseAccess::query()->sole();
        $this->assertSame(AccessSource::Admin, $access->access_source);
        $this->assertTrue($access->is_active);
        $this->assertNull($access->payment_id);

        $component
            ->callAction(TestAction::make('revoke')->table($access))
            ->assertHasNoActionErrors();

        $this->assertFalse($access->fresh()->is_active);
    }

    public function test_admin_can_unlock_a_course_from_the_student_list(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create(['level_id' => $student->level_id]);
        $course->departments()->attach($student->department_id);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListStudents::class)
            ->callAction(TestAction::make('unlockCourse')->table($student), [
                'course_id' => $course->id,
                'expires_at' => null,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('course_access', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'access_source' => AccessSource::Admin->value,
            'expires_at' => null,
            'is_active' => true,
        ]);
    }

    public function test_payment_access_cannot_be_revoked_from_student_management(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $access = CourseAccess::factory()->for($student)->create([
            'access_source' => AccessSource::Payment,
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CourseAccessesRelationManager::class, [
                'ownerRecord' => $student,
                'pageClass' => EditStudent::class,
            ])
            ->assertActionHidden(TestAction::make('revoke')->table($access));
    }

    public function test_student_cannot_open_admin_student_management(): void
    {
        $student = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs($student)
            ->get(ListStudents::getUrl(panel: 'admin'))
            ->assertForbidden();
    }
}
