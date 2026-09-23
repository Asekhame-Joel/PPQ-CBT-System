<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Department;
use App\Models\Level;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CourseFoundationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_course_belongs_to_a_level_and_multiple_departments(): void
    {
        $level = Level::factory()->create();
        $departments = Department::factory()->count(2)->create();
        $course = Course::factory()->for($level)->create();

        $course->departments()->attach($departments);

        $this->assertTrue($course->level->is($level));
        $this->assertCount(2, $course->departments);
        $this->assertTrue($departments->every(
            fn (Department $department): bool => $department->courses->contains($course),
        ));
    }

    public function test_active_scope_excludes_inactive_courses(): void
    {
        $activeCourse = Course::factory()->create();
        Course::factory()->inactive()->create();

        $course = Course::active()->sole();

        $this->assertTrue($course->is($activeCourse));
    }

    public function test_price_and_practice_settings_use_expected_types(): void
    {
        $course = Course::factory()->create([
            'price' => 1000,
            'default_question_count' => 50,
            'default_duration' => 30,
        ]);

        $this->assertSame('1000.00', $course->price);
        $this->assertSame(50, $course->default_question_count);
        $this->assertSame(30, $course->default_duration);
        $this->assertTrue($course->is_active);
    }
}
