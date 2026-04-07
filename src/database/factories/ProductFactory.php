<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => 1, // 假設妳已經有一個 id 為 1 的分類
            'name' => '測試商品-' . fake()->unique()->word(),
            'description' => fake()->realText(50),
            'price' => fake()->numberBetween(100, 5000),
            'stock' => fake()->numberBetween(0, 100),
            'image' => "https://picsum.photos/seed/" . fake()->unique()->numberBetween(1, 1000) . "/300/200",
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
