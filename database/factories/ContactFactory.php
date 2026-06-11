<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()->id,
            'first_name' => fake('ja_JP')->firstName(),
            'last_name' => fake('ja_JP')->lastName(),
            'gender' => fake()->numberBetween(1, 3),
            'email' => fake()->unique()->safeEmail(),
            'tel' => fake()->numerify('090########'),
            'address' => fake('ja_JP')->address(),
            'building' => fake('ja_JP')->secondaryAddress(),
            'detail' => fake('ja_JP')->realText(100),
        ];
    }
}
