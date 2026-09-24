<?php

namespace Tests\Feature\Student;

use App\Filament\Student\Pages\MyCourses;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Question;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MyCoursesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_sees_only_courses_with_current_access(): void
    {
        $student = User::factory()->create();
        $availableCourse = Course::factory()->create([
            'code' => 'CSC101',
            'name' => 'Introduction to Computing',
            'min_question_count' => 2,
        ]);
        $expiredCourse = Course::factory()->create([
            'code' => 'MTH101',
            'name' => 'Elementary Mathematics',
        ]);
        $inactiveCourse = Course::factory()->inactive()->create([
            'code' => 'PHY101',
            'name' => 'General Physics',
        ]);
        $otherStudentCourse = Course::factory()->create([
            'code' => 'GST101',
            'name' => 'Use of English',
        ]);

        CourseAccess::factory()->for($student)->for($availableCourse)->create();
        CourseAccess::factory()->for($student)->for($expiredCourse)->create([
            'expires_at' => now()->subMinute(),
        ]);
        CourseAccess::factory()->for($student)->for($inactiveCourse)->create();
        CourseAccess::factory()->for($otherStudentCourse)->create();
        Question::factory()->count(2)->for($availableCourse)->create();
        Question::factory()->inactive()->for($availableCourse)->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(MyCourses::class)
            ->assertSee('CSC101')
            ->assertSee('Introduction to Computing')
            ->assertSee('2')
            ->assertSee('Set up practice')
            ->assertDontSee('MTH101')
            ->assertDontSee('PHY101')
            ->assertDontSee('GST101');
    }

    public function test_student_without_access_sees_the_empty_state(): void
    {
        $student = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(MyCourses::class)
            ->assertSee('No unlocked courses yet');
    }

    public function test_admin_cannot_open_student_my_courses_page(): void
    {
        $admin = User::factory()->admin()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->actingAs($admin)
            ->get(MyCourses::getUrl(panel: 'student'))
            ->assertForbidden();
    }
}
