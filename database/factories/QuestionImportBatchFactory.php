<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\QuestionImportBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionImportBatch>
 */
class QuestionImportBatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'imported_by' => User::factory()->admin(),
            'source_filename' => 'questions.txt',
            'source_format' => 'txt',
            'question_count' => fake()->numberBetween(1, 100),
        ];
    }
}
