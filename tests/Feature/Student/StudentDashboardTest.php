<?php

namespace Tests\Feature\Student;

use App\Filament\Student\Pages\Dashboard;
use App\Filament\Student\Widgets\StudentAttemptActivity;
use App\Filament\Student\Widgets\StudentStatsOverview;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_panel_uses_the_custom_dashboard(): void
    {
        $student = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->actingAs($student)
            ->get(Dashboard::getUrl(panel: 'student'))
            ->assertSuccessful()
            ->assertSee('Dashboard');
    }

    public function test_stats_include_only_the_signed_in_students_current_data(): void
    {
        $student = User::factory()->create();
        $courseOne = Course::factory()->create();
        $courseTwo = Course::factory()->create();
        $expiredCourse = Course::factory()->create();
        CourseAccess::factory()->for($student)->for($courseOne)->create();
        CourseAccess::factory()->for($student)->for($courseTwo)->create();
        CourseAccess::factory()->for($student)->for($expiredCourse)->create([
            'expires_at' => now()->subMinute(),
        ]);
        QuizAttempt::factory()->for($student)->for($courseOne)->submitted()->create([
            'score_percentage' => 60,
        ]);
        QuizAttempt::factory()->for($student)->for($courseTwo)->submitted()->create([
            'score_percentage' => 80,
        ]);
        QuizAttempt::factory()->submitted()->create(['score_percentage' => 100]);
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(StudentStatsOverview::class)
            ->assertSee('Unlocked courses')
            ->assertSee('Completed attempts')
            ->assertSee('Average score')
            ->assertSee('70.00%')
            ->assertSee('Best score')
            ->assertSee('80.00%');
    }

    public function test_activity_shows_resumable_and_recent_attempts_for_the_student(): void
    {
        $student = User::factory()->create();
        $activeCourse = Course::factory()->create([
            'code' => 'CSC101',
            'name' => 'Introduction to Computing',
        ]);
        $completedCourse = Course::factory()->create([
            'code' => 'MTH101',
            'name' => 'Elementary Mathematics',
        ]);
        $activeAttempt = QuizAttempt::factory()->for($student)->for($activeCourse)->create([
            'question_count' => 2,
            'expires_at' => now()->addMinutes(20),
        ]);
        $answeredQuestion = AttemptQuestion::factory()->for($activeAttempt)->create(['position' => 1]);
        AttemptAnswer::factory()->for($answeredQuestion)->create(['selected_option_id' => 101]);
        AttemptQuestion::factory()->for($activeAttempt)->create(['position' => 2]);
        QuizAttempt::factory()->for($student)->for($completedCourse)->submitted()->create([
            'score_percentage' => 75,
            'submitted_at' => now(),
        ]);
        QuizAttempt::factory()->create([
            'expires_at' => now()->addMinutes(20),
        ]);
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(StudentAttemptActivity::class)
            ->assertSee('CSC101')
            ->assertSee('1 of 2 answered')
            ->assertSee('MTH101')
            ->assertSee('75.00%');

    }
}
