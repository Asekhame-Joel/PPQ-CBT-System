<?php

namespace Database\Factories;

use App\Enums\AccessSource;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseAccess>
 */
class CourseAccessFactory extends Factory
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
            'course_id' => Course::factory(),
            'payment_id' => null,
            'access_source' => AccessSource::Admin,
            'granted_at' => now(),
            'expires_at' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
