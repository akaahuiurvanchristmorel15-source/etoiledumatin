<?php

namespace Database\Factories;

use App\Enums\NewsType;
use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->sentence(5), '.');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'type' => fake()->randomElement(NewsType::cases()),
            'excerpt' => fake()->sentence(18),
            'content' => implode("\n\n", fake()->paragraphs(3)),
            'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
            'is_published' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now()->addWeek(),
        ]);
    }

    public function event(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => NewsType::Event,
            'event_date' => now()->addDays(fake()->numberBetween(5, 40))->setTime(18, 0),
            'location' => 'Abidjan, Plateau',
        ]);
    }
}
