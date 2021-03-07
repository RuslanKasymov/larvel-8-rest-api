<?php

namespace Tests;

use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\ForgotPasswordNotification;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\AuthTestTrait;

class AuthTest extends TestCase
{
    use AuthTestTrait;

    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::find(1);
    }

    public function testLogin()
    {
        $response = $this->json('post', 'auth/login', [
            'email' => 'one@example.com',
            'password' => '123456'
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'user' => [
                    'id' => 1,
                    'name' => 'Edward Orn',
                    'email' => 'one@example.com'
                ]
            ]);
    }

    public function testLoginWithIncorrectPassword()
    {
        $response = $this->json('post', 'auth/login', [
            'email' => 'one@example.com',
            'password' => 'secret1'
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testRefresh()
    {
        $response = $this->actingAs($this->user)->json('get', 'auth/refresh');

        $response->assertStatus(Response::HTTP_OK);

        $auth = $response->headers->get('authorization');
        $explodedHeader = explode(' ', $auth);

        $this->assertNotEquals($this->jwt, last($explodedHeader));
    }

    public function testLogout()
    {
        $response = $this->actingAs($this->user)->json('post', 'auth/logout');

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testForgotPassword()
    {
        $this->mockUniqueTokenGeneration('some_token');

        $response = $this->json('post', '/auth/forgot-password', [
            'email' => 'one@example.com'
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('password_resets', [
            'email' => 'one@example.com',
            'token' => null
        ]);

        $this->assertNotificationEquals($this->user, ForgotPasswordNotification::class, [
            'mail' => [
                'subject' => 'Password reset',
                'fixture' => 'forgot_password_email.html'
            ],
        ]);
    }

    public function testRestorePassword()
    {
        $this->user = PasswordReset::factory()->create([
            'email' => 'one@example.com',
            'token' => 'restore_token',
        ]);

        $response = $this->json('post', '/auth/reset-password', [
            'password' => 'new_password',
            'token' => 'restore_token',
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('password_resets', [
            'email' => 'one@example.com',
            'token' => 'restore_token'
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'one@example.com',
            'password' => '$2y$04$2lGkEKRiFe5eYsEP5cD1BeLH0dbSnh23xtpgsKG3Cp3dCbRmHnTPa'
        ]);
    }
}
