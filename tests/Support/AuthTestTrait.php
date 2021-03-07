<?php

namespace Tests\Support;

use App\Services\ResetPasswordService;

trait AuthTestTrait
{
    use MockClassTrait;

    public function mockUniqueTokenGeneration($hash)
    {
        $this->mockClass(ResetPasswordService::class, [
            ['method' => 'generateUniqueHash', 'result' => $hash]
        ]);
    }
}
