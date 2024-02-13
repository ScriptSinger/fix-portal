<?php

namespace Database\Factories;

use App\Models\Appliance;
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
        $appliances = ['стиральная машина', 'холодильник', 'посудомоечная машина', 'морозильник'];

        return [
            'title' => $this->faker->unique()->randomElement($appliances),
            'slug' => $this->faker->unique()->slug,
        ];
    }
}
