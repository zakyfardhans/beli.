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
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'no_hp' => '08' . fake()->numerify('##########'),
            'alamat' => fake()->address(),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Set role eksplisit setelah create (role tidak di $fillable).
     */
    public function role(string $role): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            $user->role = $role;
            $user->save();
        });
    }

    public function admin(): static
    {
        return $this->role('admin');
    }

    public function pelanggan(): static
    {
        return $this->role('pelanggan');
    }
}
