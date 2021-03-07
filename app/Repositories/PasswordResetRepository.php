<?php

namespace App\Repositories;

use App\Models\PasswordReset;

class PasswordResetRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(PasswordReset::class);
    }
}
