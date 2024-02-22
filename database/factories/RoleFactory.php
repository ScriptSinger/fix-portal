<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */


    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['мастер', 'заказчик', 'гость'])
        ];
    }

    public function master(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Мастер',
            ];
        });
    }

    public function participant(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Участник',
            ];
        });
    }
}
