<?php

namespace Database\Factories;

use App\Models\AudioTrack;
use App\Models\BookPart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AudioTrack>
 */
class AudioTrackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_part_id' => BookPart::factory(),
            'language' => 'fr',
            'path' => config('audio.directory').'/'.fake()->uuid().'.mp3',
            'duration_seconds' => fake()->numberBetween(300, 3600),
        ];
    }
}
