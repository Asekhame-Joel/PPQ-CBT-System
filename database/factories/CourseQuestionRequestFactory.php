<?php

namespace Database\Factories;

use App\Enums\CourseQuestionRequestStatus;
use App\Models\CourseQuestionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseQuestionRequest>
 */
class CourseQuestionRequestFactory extends Factory
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
            'course_code' => strtoupper(fake()->bothify('???###')),
            'course_name' => fake()->words(4, true),
            'message' => fake()->optional()->paragraph(),
            'status' => CourseQuestionRequestStatus::Requested,
        ];
    }
}
