<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'base_price' => $this->faker->randomFloat(2, 10, 1000),
            'sku' => $this->faker->unique()->bothify('SKU-####'),
            'category' => 'general',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 