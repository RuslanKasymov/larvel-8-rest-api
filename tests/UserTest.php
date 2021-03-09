<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends TestCase
{
    protected $admin;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::find(1);
        $this->user = User::find(1);
    }

    public function testCreateUser()
    {
        $data = $this->getJsonFixture('create_user.json');

        $response = $this->actingAs($this->admin)->json('post', '/users', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('users', Arr::except($data, 'password'));
    }

    public function testCreateNoAuth()
    {
        $data = $this->getJsonFixture('create_user.json');

        $response = $this->json('post', '/users', $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateUserExists()
    {
        $response = $this->actingAs($this->admin)->json('post', '/users', $this->user->toArray());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateUser()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->actingAs($this->admin)->json('put', '/users/2', $data);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', Arr::except($data, ['password', 'confirm']));
    }

    public function testUpdateNotExists()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->actingAs($this->admin)->json('put', '/users/0', $data);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateNoAuth()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->json('put', '/users/1', $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testUpdateProfile()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->actingAs($this->admin)->json('put', '/profile', $data);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testDelete()
    {
        $response = $this->actingAs($this->admin)->json('delete', '/users/2');

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteNoAuth()
    {
        $response = $this->json('delete', '/users/1');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetProfile()
    {
        $response = $this->actingAs($this->admin)->json('get', '/profile');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture('get_user.json', $response->json());
    }

    public function testGetNotExists()
    {
        $response = $this->actingAs($this->admin)->json('get', '/users/0');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function getListFilters()
    {
        return [
            [
                'filter' => ['role_id' => 1],
                'result' => 'list_all.json'
            ],
            [
                'filter' => [
                    'page' => 2,
                    'per_page' => 1
                ],
                'result' => 'list_by_page_per_page.json'
            ],
            [
                'filter' => [
                    'with' => ['role'],
                    'with_count' => ['role']
                ],
                'result' => 'list_with_relations.json'
            ],
            [
                'filter' => [
                    'query' => 'rha'
                ],
                'result' => 'list_by_search_query.json'
            ]
        ];
    }

    /**
     * @dataProvider  getListFilters
     *
     * @param array $filter
     * @param string $fixture
     */
    public function testList($filter, $fixture)
    {
        $response = $this->actingAs($this->admin)->json('get', '/users', $filter);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture($fixture, $response->json());
    }
}
