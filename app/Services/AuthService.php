<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
    protected $userService;

    public function __construct()
    {
        $this->userService = app(UserService::class);
    }

    public function register($registerData)
    {
        $user = $this->userService->create(array_merge(
            Arr::only($registerData, ['email', 'name']),
            ['password' => Hash::make($registerData['password'])]
        ));

        $token = Auth::attempt(Arr::only($registerData, ['email', 'password']));

        return [$token, $user];
    }

    public function respondWithToken($token, $user)
    {
        return response()
            ->json([
                'token' => $token,
                'ttl' => config('jwt.ttl'),
                'refresh_ttl' => config('jwt.refresh_ttl'),
                'user' => $user
            ], Response::HTTP_OK, ['Authorization' => $token]);
    }
}
