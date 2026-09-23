<?php

namespace Database\Factories;

use App\Models\StudentSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentSetting>
 */
class StudentSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'preferred_question_count' => null,
            'preferred_duration' => null,
            'randomize_questions' => true,
            'randomize_options' => true,
        ];
    }
}
