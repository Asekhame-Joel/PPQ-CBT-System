<?php

namespace Tests\Feature;

use App\Models\StudentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StudentSettingFoundationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_has_one_practice_setting_record(): void
    {
        $student = User::factory()->create();
        $setting = StudentSetting::factory()->for($student)->create();

        $this->assertTrue($student->studentSetting->is($setting));
        $this->assertTrue($setting->user->is($student));
        $this->assertNull($setting->preferred_question_count);
        $this->assertNull($setting->preferred_duration);
        $this->assertTrue($setting->randomize_questions);
        $this->assertTrue($setting->randomize_options);
    }

    public function test_practice_preferences_are_cast_to_expected_types(): void
    {
        $setting = StudentSetting::factory()->create([
            'preferred_question_count' => 30,
            'preferred_duration' => 20,
            'randomize_questions' => false,
            'randomize_options' => false,
        ]);

        $this->assertSame(30, $setting->preferred_question_count);
        $this->assertSame(20, $setting->preferred_duration);
        $this->assertFalse($setting->randomize_questions);
        $this->assertFalse($setting->randomize_options);
    }
}
