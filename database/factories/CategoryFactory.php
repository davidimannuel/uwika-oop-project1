<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement([
          'travel cost', 'home rent', 'food expenses', 'utility bills', 'entertainment expenses', 
          'salary income', 'investment income', 'gift income', 'rental income', 'business expenses',
          'education expenses', 'medical bills', 'car expenses', 'clothing expenses', 'phone bills'
        ]);
        // concat name with unique number to avoid duplicate name
        return [
            'name' => $name . ' ' . $this->faker->unique()->numberBetween(1, 100),
        ];
    }
}
