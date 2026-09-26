<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeedLoadTestDataTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_creates_an_unlocked_local_load_test_dataset(): void
    {
        $this->artisan('exam:seed-load-test', ['--students' => 2, '--questions' => 4])
            ->expectsOutput('Load-test data is ready: 4 questions and 2 unlocked students.')
            ->assertExitCode(0);

        $course = Course::query()->where('code', 'LOAD101')->sole();
        $student = User::query()->where('email', 'loadtest+1@example.test')->sole();

        $this->assertSame(4, $course->questions()->count());
        $this->assertSame(16, $course->questions()->first()->options()->count() * 4);
        $this->assertTrue(Hash::check('load-test-password', $student->password));
        $this->assertDatabaseHas('course_access', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'is_active' => true,
        ]);
    }
}
