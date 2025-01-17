<?php

namespace Database\Factories;

use App\Models\Manager;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManagerFactory extends Factory
{
    protected $model = Manager::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'phone' => $this->faker->numerify('##########'),
            'address' => $this->faker->address,
            'company_id' => 1,
        ];
    }
}