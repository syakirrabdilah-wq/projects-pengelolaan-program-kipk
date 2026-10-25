<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->numerify('##########'),
            'password' => static::$password ??= 'password',
            'role' => 'mahasiswa',
            'aktif' => true,
            'remember_token' => Str::random(10),
        ];
    }
}
