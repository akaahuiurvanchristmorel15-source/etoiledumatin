<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'description' => fake()->sentence(),
            'permissions' => [],
        ];
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public function withPermissions(array $permissions): static
    {
        return $this->state(fn () => ['permissions' => $permissions]);
    }
}
