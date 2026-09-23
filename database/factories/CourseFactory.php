<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'code' => fake()->unique()->bothify('??? ###'),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'price' => fake()->randomElement([500, 1000, 1500, 2000]),
            'default_question_count' => 50,
            'default_duration' => 30,
            'min_question_count' => 10,
            'max_question_count' => 100,
            'min_duration' => 10,
            'max_duration' => 120,
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
