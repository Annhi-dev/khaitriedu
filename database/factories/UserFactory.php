<?php

namespace Database\Factories;

use App\Models\VaiTro;
use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = NguoiDung::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'role_id' => VaiTro::idByName(NguoiDung::ROLE_STUDENT),
            'status' => NguoiDung::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => VaiTro::idByName(NguoiDung::ROLE_ADMIN),
        ]);
    }

    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => VaiTro::idByName(NguoiDung::ROLE_TEACHER),
        ]);
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => VaiTro::idByName(NguoiDung::ROLE_STUDENT),
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => NguoiDung::STATUS_LOCKED,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => NguoiDung::STATUS_INACTIVE,
        ]);
    }
}
