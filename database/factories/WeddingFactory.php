<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Template;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WeddingFactory extends Factory
{
    protected $model = Wedding::class;

    public function definition(): array
    {
        $groom = $this->faker->firstName('male');
        $bride = $this->faker->firstName('female');

        return [
            'client_id'   => Client::factory(),
            'template_id' => Template::factory(),
            'public_id'   => 'INV-' . strtoupper(Str::random(6)),
            'slug'        => Str::slug("{$groom}-{$bride}") . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'title'       => "The Wedding of {$groom} & {$bride}",
            'groom_name'  => $groom . ' ' . $this->faker->lastName(),
            'bride_name'  => $bride . ' ' . $this->faker->lastName(),
            'groom_nickname' => $groom,
            'bride_nickname' => $bride,
            'date'        => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'venue'       => $this->faker->company() . ' Hall',
            'address'     => $this->faker->address(),
            'status'      => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => 'published', 'published_at' => now()]);
    }
}
