<?php

namespace App\Services;

use App\Notifications\ForgotPasswordNotification;
use App\Repositories\PasswordResetRepository;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResetPasswordService extends BaseService
{
    protected $userService;

    public function __construct()
    {
        parent::__construct();

        $this->setRepository(PasswordResetRepository::class);
        $this->userService = app(UserService::class);
    }

    public function forgotPassword(string $email): void
    {
        $hash = $this->generateUniqueHash();
        $user = $this->userService->first(['email' => $email]);

        $passwordReset = $this->repository->updateOrCreate(['email' => $email], [
            'token' => $hash
        ]);

        $user->notify(new ForgotPasswordNotification($passwordReset->token));
    }

    public function resetPassword(string $token, string $password): void
    {
        $resetPasswordEntry = $this->first(['token' => $token]);
        $user = $this->userService->first(['email' => $resetPasswordEntry->email]);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $this->userService->update($user->id, ['password' => $password]);
        $this->repository->delete(['email' => $user->email]);
    }

    protected function generateUniqueHash($length = 16): string
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}
