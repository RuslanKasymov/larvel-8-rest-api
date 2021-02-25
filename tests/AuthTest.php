<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthTest extends TestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Edward Orn',
            'email' => 'one@example.com',
            'password' => Hash::make('123456'),
            'role_id' => 1,
        ]);
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
                    'email' => 'one@example.com',
                    'created_at' => '2018-11-11T11:11:11.000000Z',
                    'updated_at' => '2018-11-11T11:11:11.000000Z'
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
}
