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
        $data = [
          'remark' => $this->faker->sentence,
          'transaction_at' => $this->faker->dateTimeThisYear,
        ];
        if ($this->faker->boolean) {
          $data['debit'] = $this->faker->randomFloat(3, 0, 1000);
        } else {
          $data['credit'] = $this->faker->randomFloat(3, 0, 1000);
        }
        return $data;
    }
}
