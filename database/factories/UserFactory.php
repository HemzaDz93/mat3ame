<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => User::ROLE_STAFF,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /** حساب معطّل */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /** حساب بدور محدّد */
    public function role(string $role): static
    {
        return $this->state(fn () => ['role' => $role]);
    }
}
