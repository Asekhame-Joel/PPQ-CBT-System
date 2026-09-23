<?php

namespace Database\Factories;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'reference' => fake()->unique()->bothify('PAY-########-????'),
            'amount' => 1000,
            'currency' => 'NGN',
            'status' => PaymentStatus::Pending,
            'provider' => PaymentProvider::Paystack,
            'paid_at' => null,
            'provider_response' => null,
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PaymentStatus::Successful,
            'paid_at' => now(),
            'provider_response' => ['status' => 'success'],
        ]);
    }
}
