<?php

namespace Database\Factories;

use App\Enums\QuestionReportReason;
use App\Enums\QuestionReportStatus;
use App\Models\AttemptQuestion;
use App\Models\QuestionReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionReport>
 */
class QuestionReportFactory extends Factory
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
            'question_id' => null,
            'attempt_question_id' => AttemptQuestion::factory(),
            'reason' => QuestionReportReason::Other,
            'report_text' => fake()->sentence(),
            'status' => QuestionReportStatus::Pending,
            'admin_notes' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => QuestionReportStatus::Resolved,
            'admin_notes' => fake()->sentence(),
            'resolved_by' => User::factory()->admin(),
            'resolved_at' => now(),
        ]);
    }
}
