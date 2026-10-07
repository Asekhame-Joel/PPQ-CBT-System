<?php

namespace Tests\Feature\Student;

use App\Filament\Student\Pages\AvailableCourses;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Department;
use App\Models\Level;
use App\Models\Question;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AvailableCoursesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_sees_active_courses_for_their_level_and_department_including_general_courses(): void
    {
        $department = Department::factory()->create();
        $otherDepartment = Department::factory()->create();
        $level = Level::factory()->create();
        $otherLevel = Level::factory()->create();
        $student = User::factory()->for($department)->for($level)->create();

        $departmentCourse = Course::factory()->for($level)->create(['code' => 'CSC101']);
        $departmentCourse->departments()->attach($department);
        Course::factory()->for($level)->create(['code' => 'GST101']);

        $otherDepartmentCourse = Course::factory()->for($level)->create(['code' => 'BIO101']);
        $otherDepartmentCourse->departments()->attach($otherDepartment);
        $otherLevelCourse = Course::factory()->for($otherLevel)->create(['code' => 'CSC201']);
        $otherLevelCourse->departments()->attach($department);
        $inactiveCourse = Course::factory()->inactive()->for($level)->create(['code' => 'CSC102']);
        $inactiveCourse->departments()->attach($department);

        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(AvailableCourses::class)
            ->assertSee('CSC101')
            ->assertSee('GST101')
            ->assertDontSee('BIO101')
            ->assertDontSee('CSC201')
            ->assertDontSee('CSC102');
    }

    public function test_catalogue_distinguishes_unlocked_locked_and_not_ready_courses(): void
    {
        $student = User::factory()->create();
        $courseAttributes = [
            'level_id' => $student->level_id,
            'min_question_count' => 2,
        ];
        $unlockedCourse = Course::factory()->create($courseAttributes + ['code' => 'CSC101']);
        $lockedCourse = Course::factory()->create($courseAttributes + ['code' => 'CSC102']);
        $notReadyCourse = Course::factory()->create($courseAttributes + ['code' => 'CSC103']);

        foreach ([$unlockedCourse, $lockedCourse, $notReadyCourse] as $course) {
            $course->departments()->attach($student->department_id);
        }

        Question::factory()->count(2)->for($unlockedCourse)->create();
        Question::factory()->count(2)->for($lockedCourse)->create();
        Question::factory()->for($notReadyCourse)->create();
        CourseAccess::factory()->for($student)->for($unlockedCourse)->create();

        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(AvailableCourses::class)
            ->assertSee('Unlocked')
            ->assertSee('Start practice')
            ->assertSee('Unlock course')
            ->assertSee('Not available yet');
    }

    public function test_catalogue_uses_the_administrator_question_count_when_one_is_set(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create([
            'code' => 'NCP1',
            'display_question_count' => 300,
            'level_id' => $student->level_id,
            'min_question_count' => 2,
        ]);
        $course->departments()->attach($student->department_id);
        Question::factory()->count(2)->for($course)->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(AvailableCourses::class)
            ->assertSee('300 questions')
            ->assertDontSee('2 questions');
    }

    public function test_students_from_any_level_assigned_to_a_course_can_see_it(): void
    {
        $department = Department::factory()->create();
        $firstLevel = Level::factory()->create();
        $secondLevel = Level::factory()->create();
        $firstStudent = User::factory()->for($department)->for($firstLevel)->create();
        $secondStudent = User::factory()->for($department)->for($secondLevel)->create();
        $course = Course::factory()->for($firstLevel)->create(['code' => 'GST111']);
        $course->levels()->sync([$firstLevel->id, $secondLevel->id]);
        $course->departments()->attach($department);
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($firstStudent)
            ->test(AvailableCourses::class)
            ->assertSee('GST111');

        Livewire::actingAs($secondStudent)
            ->test(AvailableCourses::class)
            ->assertSee('GST111');
    }

    public function test_student_with_incomplete_academic_profile_sees_guidance(): void
    {
        $student = User::factory()->create([
            'department_id' => null,
            'level_id' => null,
        ]);
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(AvailableCourses::class)
            ->assertSee('Complete your academic profile')
            ->assertSee('department and level are required');
    }

    public function test_admin_cannot_open_student_available_courses_page(): void
    {
        $admin = User::factory()->admin()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->actingAs($admin)
            ->get(AvailableCourses::getUrl(panel: 'student'))
            ->assertForbidden();
    }
}
