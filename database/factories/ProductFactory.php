<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->text(100),
            'price' => fake()->randomNumber(5, true),
            'image' => fake()->imageUrl(),
            'category_id' => fake()->numberBetween(1, 10),
            'expired_at' => fake()->date(),
        ];
    }
}
