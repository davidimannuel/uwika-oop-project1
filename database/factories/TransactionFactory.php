<?php

namespace Database\Factories;

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
        return [
            'remark' => $this->faker->sentence,
            'debit' => $this->faker->randomFloat(3, 0, 1000),
            'credit' => $this->faker->randomFloat(3, 0, 1000),
        ];
    }
}
