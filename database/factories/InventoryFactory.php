<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'stock' => $this->faker->randomNumber(2),
            'stock_in' => $this->faker->randomNumber(2),
            'stock_out' => $this->faker->randomNumber(2),
            'price' => $this->faker->randomNumber(5),
            'weight' => $this->faker->randomNumber(2),
            'image' => $this->faker->imageUrl(),
            'minimum_stock' => $this->faker->randomNumber(2),
        ];
    }
}
