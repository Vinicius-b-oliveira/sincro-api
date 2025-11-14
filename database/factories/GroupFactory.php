<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->words(rand(2, 4), true),
            'description' => fake()->paragraph(rand(1, 2)),
            'members_can_add_transactions' => fake()->boolean(),
            'members_can_invite' => fake()->boolean(),
        ];
    }
}
