<?php

namespace App\Repositories;

use App\Models\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetRepository
{
    public function updateOrCreate(string $email)
    {
        return PasswordReset::updateOrCreate(['email' => $email], ['token' => Str::random(60)]);
    }

    public function first(array $options)
    {
        return PasswordReset::where($options)->first();
    }

    public function delete(array $options)
    {
        PasswordReset::where($options)->delete();
    }
}
