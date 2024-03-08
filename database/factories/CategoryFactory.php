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

    private $categoryIndex = 0;
    public function definition(): array
    {
        $categories = [
            'Ремонт холодильников',
            'Ремонт стиральных машин',
            'Коды ошибок стиральных машин',
            'Коды ошибок холодильников',
            'Характеристики компрессоров',
            'Вопросы по ремонту бытовой техники',
        ];

        $title = $categories[$this->categoryIndex];
        $this->categoryIndex++;


        $slug = SlugService::createSlug(Category::class, 'slug', $title);

        return [
            'title' => $title,
            'slug' => $slug,
        ];
    }
}
