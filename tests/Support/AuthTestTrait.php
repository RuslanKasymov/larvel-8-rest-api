<?php

namespace Tests\Support;

use App\Repositories\PasswordResetRepository;
use App\Services\ResetPasswordService;
use App\Services\UserService;

trait AuthTestTrait
{
    use MockClassTrait;

    public function mockUniqueTokenGeneration($hash)
    {
        $this->mockClassPartial(
            ResetPasswordService::class,
            [
                ['method' => 'generateUniqueHash', 'result' => $hash]
            ],
            [
                app(PasswordResetRepository::class),
                app(UserService::class)
            ]
        );
    }
}
