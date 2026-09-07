<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        return [
            'key'        => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'name'       => ucwords($name),
            'is_active'  => true,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
