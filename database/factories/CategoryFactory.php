<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use Cviebrock\EloquentSluggable\Services\SlugService;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Category::class;


    public function definition(): array
    {

        $categories = ['Коды ошибок стиральных машин', 'Коды ошибок холодильников', 'Характеристики компрессоров', 'Ремонт стиральных машин', 'Ремонт холодильников'];


        return [
            'title' => $this->faker->unique()->randomElement($categories),
            'slug' => function (array $attributes) {
                return SlugService::createSlug(Category::class, 'slug', $attributes['title']);
            },
        ];
    }
}
