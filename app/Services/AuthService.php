<?php

namespace App\Services;

use App\Models\Role;
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
        $registerData['role_id'] = Role::USER;

        $user = $this->userService->create($registerData);

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
