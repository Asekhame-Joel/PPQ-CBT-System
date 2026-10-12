<?php

namespace Database\Factories;

use App\Models\DeviceSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeviceSession>
 */
class DeviceSessionFactory extends Factory
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
            'device_key_hash' => hash('sha256', Str::random(64)),
            'session_id_hash' => hash('sha256', Str::random(40)),
            'device_name' => 'Chrome on Windows',
            'user_agent' => $this->faker->userAgent(),
            'ip_address' => $this->faker->ipv4(),
            'signed_in_at' => now(),
            'last_seen_at' => now(),
            'signed_out_at' => null,
            'disabled_at' => null,
            'disabled_by' => null,
        ];
    }
}
