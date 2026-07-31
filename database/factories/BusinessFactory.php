<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name'     => $name,
            'slug'     => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(4),
            'phone'    => $this->faker->numerify('+1##########'),
            'currency' => 'USD',
            'status'   => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
