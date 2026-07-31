<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->unique()->words(3, true),
            'sku'         => strtoupper(Str::random(8)),
            'description' => $this->faker->sentence(10),
            'price'       => number_format($this->faker->randomFloat(2, 1, 999), 2, '.', ''),
            'stock'       => $this->faker->numberBetween(0, 200),
            'is_active'   => true,
            'image_path'  => null,
            // business_id and category_id must be set externally
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
