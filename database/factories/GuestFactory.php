<?php

namespace Database\Factories;

use App\Models\Guest;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestFactory extends Factory
{
    protected $model = Guest::class;

    public function definition(): array
    {
        return [
            'wedding_id' => Wedding::factory(),
            'name'       => $this->faker->name(),
            'phone'      => $this->faker->phoneNumber(),
            'email'      => $this->faker->safeEmail(),
            'max_pax'    => $this->faker->numberBetween(1, 4),
            'status'     => 'pending',
        ];
    }
}
