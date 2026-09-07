<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'role'              => 'client_admin',
            'is_active'         => true,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(['role' => 'super_admin', 'client_id' => null]);
    }

    public function clientAdmin(?int $clientId = null): static
    {
        return $this->state(fn() => [
            'role'      => 'client_admin',
            'client_id' => $clientId ?? Client::factory(),
        ]);
    }

    public function operator(?int $clientId = null): static
    {
        return $this->state(fn() => [
            'role'      => 'checkin_operator',
            'client_id' => $clientId ?? Client::factory(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
