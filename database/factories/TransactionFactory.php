<?php

namespace Database\Factories;

use App\Enums\TransactionCategory;
use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(TransactionType::cases());

        if ($type === TransactionType::EXPENSE) {
            $category = fake()->randomElement(TransactionCategory::expense());
        } else {
            $category = fake()->randomElement(TransactionCategory::income());
        }

        return [
            'user_id' => User::factory(),

            'group_id' => null,

            'title' => fake()->sentence(rand(2, 5)),

            'description' => fake()->paragraph(rand(1, 3)),

            'amount' => fake()->randomFloat(2, 5, 1000),

            'type' => $type,

            'category' => $category,

            'transaction_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
