<?php

namespace Database\Factories;

use App\Models\PasswordReset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PasswordResetFactory extends Factory
{
    protected $model = PasswordReset::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'token' => Str::random(16)
        ];
    }
}
