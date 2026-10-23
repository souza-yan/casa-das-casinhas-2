<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 20, 500),
            'image_path' => null,
            'weight_kg' => fake()->randomFloat(3, 0.1, 5),
            'height_cm' => fake()->randomFloat(2, 2, 100),
            'width_cm' => fake()->randomFloat(2, 2, 100),
            'length_cm' => fake()->randomFloat(2, 2, 100),
            'production_days' => Product::PRODUCTION_DAYS,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
