<?php

namespace Database\Factories;

use App\Models\Appliance;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appliance>
 */
class ApplianceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Appliance::class;

    public function definition(): array
    {
        $appliances = ['Стиральные Машины', 'Холодильники', 'Посудомоечные Машины', 'Морозильники'];

        return [
            'title' => $this->faker->unique()->randomElement($appliances),
            'slug' => function (array $attributes) {
                return SlugService::createSlug(Appliance::class, 'slug', $attributes['title']);
            },
        ];
    }
}
