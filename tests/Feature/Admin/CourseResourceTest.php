<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Models\Course;
use App\Models\Department;
use App\Models\Level;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_list_courses(): void
    {
        $admin = User::factory()->admin()->create();
        $courses = Course::factory()->count(3)->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListCourses::class)
            ->assertCanSeeTableRecords($courses);
    }

    public function test_admin_can_create_course_with_department_assignments(): void
    {
        $admin = User::factory()->admin()->create();
        $level = Level::factory()->create();
        $departments = Department::factory()->count(2)->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateCourse::class)
            ->fillForm($this->validCourseData($level, $departments->modelKeys()))
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $course = Course::where('code', 'CSC 111')->sole();

        $this->assertSame('Introduction to Computing', $course->name);
        $this->assertSame('1000.00', $course->price);
        $this->assertTrue($course->level->is($level));
        $this->assertEqualsCanonicalizing($departments->modelKeys(), $course->departments->modelKeys());
    }

    public function test_admin_can_update_course_and_department_assignments(): void
    {
        $admin = User::factory()->admin()->create();
        $level = Level::factory()->create();
        $oldDepartment = Department::factory()->create();
        $newDepartment = Department::factory()->create();
        $course = Course::factory()->for($level)->create(['code' => 'CSC 111']);
        $course->departments()->attach($oldDepartment);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(EditCourse::class, ['record' => $course->id])
            ->fillForm($this->validCourseData($level, [$newDepartment->id]))
            ->call('save')
            ->assertHasNoFormErrors();

        $course->refresh();

        $this->assertEqualsCanonicalizing([$newDepartment->id], $course->departments->modelKeys());
        $this->assertSame('CSC 111', $course->code);
    }

    public function test_course_defaults_must_be_within_configured_ranges(): void
    {
        $admin = User::factory()->admin()->create();
        $level = Level::factory()->create();
        $department = Department::factory()->create();
        $data = $this->validCourseData($level, [$department->id]);
        $data['min_question_count'] = 20;
        $data['default_question_count'] = 10;
        $data['default_duration'] = 130;
        $data['max_duration'] = 120;
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateCourse::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasFormErrors([
                'default_question_count' => 'gte',
                'default_duration' => 'lte',
            ]);

        $this->assertDatabaseMissing('courses', ['code' => 'CSC 111']);
    }

    public function test_admin_cannot_create_duplicate_course_code(): void
    {
        $admin = User::factory()->admin()->create();
        $level = Level::factory()->create();
        $department = Department::factory()->create();
        Course::factory()->create(['code' => 'CSC 111']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateCourse::class)
            ->fillForm($this->validCourseData($level, [$department->id]))
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);

        $this->assertSame(1, Course::where('code', 'CSC 111')->count());
    }

    /**
     * @param  array<int, int>  $departmentIds
     * @return array<string, mixed>
     */
    private function validCourseData(Level $level, array $departmentIds): array
    {
        return [
            'level_id' => $level->id,
            'departments' => $departmentIds,
            'code' => 'CSC 111',
            'name' => 'Introduction to Computing',
            'description' => 'Foundational computing concepts.',
            'price' => 1000,
            'min_question_count' => 10,
            'default_question_count' => 50,
            'max_question_count' => 100,
            'min_duration' => 10,
            'default_duration' => 30,
            'max_duration' => 120,
            'is_active' => true,
        ];
    }
}
