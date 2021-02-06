<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use App\Services\ResetPasswordService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $service)
    {
        $this->authService = $service;
    }

    public function register(RegisterRequest $request, AuthService $service)
    {
        list($token, $user) = $this->authService->register($request->only('name', 'email', 'password'));

        return $service->respondWithToken($token, $user);
    }

    public function login(LoginRequest $request, AuthService $service)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'message' => trans('auth.failed'),
                'errors' => [
                    'password' => [trans('auth.failed')]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $service->respondWithToken($token, Auth::user());
    }

    public function logout(LogoutRequest $request, JWTAuth $jwt)
    {
        $jwt->invalidate($jwt->getToken());

        return response()->json('', Response::HTTP_NO_CONTENT);
    }

    public function refresh(RefreshTokenRequest $request, AuthService $service)
    {
        $user = Auth::user();
        $token = auth()->tokenById($user->id);

        return $service->respondWithToken($token, $user);
    }

    public function forgotPassword(ForgotPasswordRequest $request, ResetPasswordService $service)
    {
        $service->forgotPassword($request->input('email'));

        return response()->json('', Response::HTTP_NO_CONTENT);
    }

    public function resetPassword(ResetPasswordRequest $request, ResetPasswordService $service)
    {
        try {
            $service->resetPassword(
                $request->input('token'),
                $request->input('password')
            );
        } catch (NotFoundHttpException $exception) {
            return response($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }
}
