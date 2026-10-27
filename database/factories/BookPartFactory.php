<?php

namespace Database\Factories;

use App\Models\BookPart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Le livre se rattache via ->for($book) : les livres proviennent du catalogue de démonstration.
 *
 * @extends Factory<BookPart>
 */
class BookPartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position' => fake()->numberBetween(1, 12),
            'title' => fake()->sentence(3),
            'summary' => fake()->optional()->paragraph(),
        ];
    }
}
