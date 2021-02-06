<?php

namespace App\Services;

use App\Notifications\ForgotPassword;
use App\Repositories\PasswordResetRepository;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResetPasswordService
{
    protected $passwordResetRepository;
    protected $userService;

    public function __construct()
    {
        $this->passwordResetRepository = app(PasswordResetRepository::class);
        $this->userService = app(UserService::class);
    }

    public function forgotPassword(string $email)
    {
        $user = $this->userService->first(['email' => $email]);
        $passwordReset = $this->passwordResetRepository->updateOrCreate($email);

        $user->notify(new ForgotPassword($passwordReset->token));
    }

    public function first(array $options)
    {
        return $this->passwordResetRepository->first($options);
    }

    public function resetPassword(string $token, string $password)
    {
        $resetPasswordEntry = $this->first(['token' => $token]);
        $user = $this->userService->first(['email' => $resetPasswordEntry->email]);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $this->userService->update($user->id, ['password' => Hash::make($password)]);
        $this->passwordResetRepository->delete(['email' => $user->email]);

        return true;
    }
}
