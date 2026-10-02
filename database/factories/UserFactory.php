<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => 'Password#123',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ];
    }

    public function administrator(): static
    {
        return $this->state(fn () => ['role' => User::ROLE_ADMIN]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => User::STATUS_INACTIVE]);
    }
}
