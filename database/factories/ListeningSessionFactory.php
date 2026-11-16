<?php

namespace Database\Factories;

use App\Models\ListeningSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListeningSession>
 */
class ListeningSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'user_agent' => fake()->userAgent(),
            'ip_address' => fake()->ipv4(),
            'started_at' => now(),
            'last_seen_at' => now(),
            'stream_credit_seconds' => 600,
            'credit_updated_at' => now(),
        ];
    }
}
