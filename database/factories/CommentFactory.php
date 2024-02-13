<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $commentableTypes = ['App\Models\Post', 'App\Models\Question', 'App\Models\Firmware'];
        return [
            'user_id' => User::factory(),
            'commentable_type' => $this->faker->randomElement($commentableTypes),
            'commentable_id' => function () {
                // Здесь вы можете вернуть логику для определения ID для commentable
                return rand(1, 20); // Пример: случайное число от 1 до 100
            },
            'text' => $this->faker->paragraph,
        ];
    }
}
