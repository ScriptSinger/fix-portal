<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'slug' => Str::slug($this->faker->sentence),
            'description' => $this->faker->paragraph,
            'content' => $this->faker->paragraph(5), // Пример для генерации 5 абзацев контента
            'category_id' => Category::factory(), // Пример для случайной категории
            'views' => $this->faker->numberBetween(0, 1000), // Пример для случайного количества просмотров
            'thumbnail' => $this->faker->imageUrl(400, 300, 'cats', true), // Пример изображения-заглушки
        ];
    }
}
