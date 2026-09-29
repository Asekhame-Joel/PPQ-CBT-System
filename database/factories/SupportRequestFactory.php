<?php

namespace Database\Factories;

use App\Enums\SupportRequestCategory;
use App\Enums\SupportRequestStatus;
use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportRequest>
 */
class SupportRequestFactory extends Factory
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
            'category' => SupportRequestCategory::GeneralHelp,
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'status' => SupportRequestStatus::Open,
        ];
    }
}
