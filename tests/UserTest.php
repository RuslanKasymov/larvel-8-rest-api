<?php

namespace Tests;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends TestCase
{
    protected $admin;
    protected $employee;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::find(1);
        $this->user = User::find(1);
    }

    public function testCreateUserAsAdmin()
    {
        $response = $this->actingAs($this->admin)->json('post', '/users', [
            'name' => 'Pavlou',
            'email' => 'createuser@example.com',
            'role_id' => 2,
            'password' => 'asdasd'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('users', [
            'name' => 'Pavlou',
            'email' => 'createuser@example.com',
            'role_id' => 2,
            'password' => 'asdasd'
        ]);
    }

    public function testCreateNoAuth()
    {
        $data = $this->getJsonFixture('create_manager.json');

        $response = $this->json('post', '/users', $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateNoPermission()
    {
        $data = $this->getJsonFixture('user.json');

        $response = $this->actingAs($this->manager)->json('post', '/users', $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testCreateUserExists()
    {
        $response = $this->actingAs($this->admin)->json('post', '/users', $this->manager->toArray());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCloneAdminAsSuperAdmin()
    {
        $response = $this->actingAs($this->superAdmin)->json('post', '/users/14/clone', [
            'email' => 'createuser+company_name@example.com',
            'company_id' => 1,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Gastro',
            'last_name' => 'Bar',
            'email' => 'createuser+company_name@example.com',
            'phone' => 612423334455,
            'role_id' => 2,
            'company_id' => 1,
            'state' => User::ACTIVE_STATE,
            'report' => '{"report":"data"}'
        ]);
    }

    public function testUpdateAdmin()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->actingAs($this->superAdmin)->json('put', '/users/2', $data);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 2,
            'first_name' => 'Another',
            'last_name' => 'User',
            'phone' => '0435725384',
            'date_of_birth' => '1997-08-19',
            'email' => 'updateuser@example.com'
        ]);
    }

    public function testUpdateDepartment()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/4', [
            'department_id' => 1
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 4,
            'department_id' => 1
        ]);
    }

    public function testUpdateDepartmentOtherCompany()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/4', [
            'department_id' => 2
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateAvatar()
    {
        $this->mockCreateMedia();

        $response = $this->actingAs($this->superAdmin)->json('put', '/users/2', [
            'first_name' => 'Another',
            'file' => $this->file
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 2,
            'first_name' => 'Another',
            'avatar_id' => 2
        ]);
    }

    public function testUpdateManager()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->actingAs($this->admin)->json('put', '/users/3', $data);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 3,
            'first_name' => 'Another',
            'last_name' => 'User',
            'phone' => '0435725384',
            'date_of_birth' => '1997-08-19',
            'email' => 'updateuser@example.com'
        ]);
    }

    public function testUpdateEmployee()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->actingAs($this->admin)->json('put', '/users/4', $data);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 4,
            'first_name' => 'Another',
            'last_name' => 'User',
            'phone' => '0435725384',
            'date_of_birth' => '1997-08-19',
            'email' => 'updateuser@example.com'
        ]);
    }

    public function testUpdateAdminAsManager()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->actingAs($this->manager)->json('put', '/users/2', $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateSuperUserAsManager()
    {
        $data = $this->getJsonFixture('user.json');

        $response = $this->actingAs($this->manager)->json('put', '/users/1', $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateSuperUserAsEmployee()
    {
        $data = $this->getJsonFixture('user.json');

        $response = $this->actingAs($this->employee)->json('put', '/users/1', $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateRoleManagerToAdminAsAdmin()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/3', [
            'role_id' => 2
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 3,
            'role_id' => 2
        ]);
    }

    public function testUpdateRoleEmployeeToManager()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/4', [
            'role_id' => 3
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 4,
            'role_id' => 3
        ]);
    }

    public function testUpdateRoleEmployeeToAdminAsAdmin()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/4', [
            'role_id' => 2
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 4,
            'role_id' => 2
        ]);
    }

    public function testUpdatePasswordForAdmin()
    {
        $response = $this->actingAs($this->superAdmin)->json('put', '/users/2', [
            'password' => 'new_password',
            'confirm' => 'new_password'
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('users', [
            'id' => 2,
            'password' => 'old_password'
        ]);
    }

    public function testUpdatePasswordForManager()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/3', [
            'password' => 'new_password',
            'confirm' => 'new_password'
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('users', [
            'id' => 3,
            'password' => 'old_password'
        ]);
    }

    public function testUpdatePasswordForEmployee()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/4', [
            'password' => 'new_password',
            'confirm' => 'new_password'
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('users', [
            'id' => 4,
            'password' => 'old_password'
        ]);
    }

    public function testUpdatePasswordForSuperAdminAsAdmin()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/1', [
            'password' => 'new_password',
            'confirm' => 'new_password'
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
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

        $response = $this->actingAs($this->superAdmin)->json('put', '/profile', $data);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testUpdateProfileAvatar()
    {
        $response = $this->actingAs($this->superAdmin)->json('put', '/profile', [
            'file' => $this->file
        ]);

        $this->assertDatabaseHas('media', [
            'id' => 3
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testUpdateProfilePassword()
    {
        $response = $this->actingAs($this->superAdmin)->json('put', '/profile', [
            'password' => 'new_password',
            'confirm' => 'new_password'
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testUpdateProfileAsAdmin()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->actingAs($this->admin)->json('put', '/profile', $data);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testUpdateProfileAsManager()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->actingAs($this->manager)->json('put', '/profile', $data);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testUpdateProfileAsEmployee()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->actingAs($this->employee)->json('put', '/profile', $data);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testUpdateProfileNoAuth()
    {
        $data = $this->getJsonFixture('update_user.json');

        $response = $this->json('put', '/profile', $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testDeleteAsSuperAdmin()
    {
        $response = $this->actingAs($this->superAdmin)->json('delete', '/users/2');

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteSuperAdmin()
    {
        $response = $this->actingAs($this->admin)->json('delete', '/users/1');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteAdminByAdmin()
    {
        $response = $this->actingAs($this->adminForth)->json('delete', '/users/7');

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('users', [
            'id' => 7,
        ]);
    }

    public function testDeleteManager()
    {
        $response = $this->actingAs($this->admin)->json('delete', '/users/3');

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('users', [
            'id' => 3,
        ]);
    }

    public function testDeleteAsManager()
    {
        $response = $this->actingAs($this->manager)->json('delete', '/users/2');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteNotExists()
    {
        $response = $this->actingAs($this->admin)->json('delete', '/users/0');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
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

    public function testGetProfileWithRelations()
    {
        $response = $this->actingAs($this->employee)->json('get', '/profile', [
            'with' => [
                'character_type',
                'avatar',
                'department',
                'company.package',
                'latest_wellness',
                'latest_reflection',
                'latest_connect_as_employee',
                'latest_completed_action_as_employee',
                'manager'
            ]
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture('get_profile_with_relations.json', $response->json());
    }

    public function testGetAdmin()
    {
        $response = $this->actingAs($this->superAdmin)->json('get', '/users/2');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture('get_user.json', $response->json());
    }

    public function testGetAdminAsManager()
    {
        $response = $this->actingAs($this->manager)->json('get', '/users/2');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testGetManagerAsSuperAdmin()
    {
        $response = $this->actingAs($this->superAdmin)->json('get', '/users/3');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testGetEmployee()
    {
        $response = $this->actingAs($this->admin)->json('get', '/users/4');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson([
            'id' => 4,
            'first_name' => 'Groot',
            'last_name' => 'Tree',
            'email' => 'groot.tree@example.com',
            'phone' => '612223334455',
            'date_of_birth' => '2019-03-08',
            'manager_id' => 3,
            'created_at' => '2016-10-20 11:05:00',
            'updated_at' => '2016-10-20 11:05:00',
            'role_id' => 4,
            'character_type_id' => 3,
            'company_id' => 1,
            'avatar_id' => 1,
            'state' => 'ACTIVE_STATE',
            'department_id' => NULL,
        ]);
    }

    public function testGetNotExists()
    {
        $response = $this->actingAs($this->admin)->json('get', '/users/0');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function getSearchFilters()
    {
        return [
            [
                'filter' => [
                    'role_id_list' => [3]
                ],
                'result' => 'get_managers.json'
            ],
            [
                'filter' => [
                    'role_id_list' => [2, 3]
                ],
                'result' => 'get_managers_and_admins.json'
            ],
            [
                'filter' => [
                    'role_id_list' => [2, 3, 4]
                ],
                'result' => 'get_managers_admins_employees.json'
            ],
            [
                'filter' => [
                    'role_id_list' => [1, 2, 3, 4]
                ],
                'result' => 'get_managers_admins_employees.json'
            ],
            [
                'filter' => [],
                'result' => 'get_by_company.json'
            ],
            [
                'filter' => ['query' => 'Groot'],
                'result' => 'get_users_by_name.json'
            ],
            [
                'filter' => ['only_trashed' => true],
                'result' => 'search_deleted_users.json'
            ],
            [
                'filter' => ['with_trashed' => true],
                'result' => 'search_with_deleted_users.json'
            ],
            [
                'filter' => [
                    'with' => [
                        'character_type',
                        'avatar',
                        'department',
                        'company.package',
                        'latest_wellness',
                        'latest_reflection',
                        'latest_connect_as_employee',
                        'latest_completed_action_as_employee',
                        'manager'
                    ],
                ],
                'result' => 'search_with.json'
            ],
            [
                'filter' => [
                    'with' => [
                        'latest_wellness',
                        'latest_reflection',
                        'latest_connect_as_employee',
                        'latest_completed_action_as_employee'
                    ],
                    'order_by' => 'latest_wellness.created_at'
                ],
                'result' => 'search_order_by_wellness_created_at.json'
            ],
            [
                'filter' => [
                    'with' => [
                        'latest_wellness',
                        'latest_reflection',
                        'latest_connect_as_employee',
                        'latest_completed_action_as_employee'
                    ],
                    'order_by' => 'latest_reflection.created_at'
                ],
                'result' => 'search_order_by_reflection_created_at.json'
            ],
            [
                'filter' => [
                    'with' => [
                        'latest_wellness',
                        'latest_reflection',
                        'latest_connect_as_employee',
                        'latest_completed_action_as_employee'
                    ],
                    'order_by' => 'latest_connect_as_employee.datetime'
                ],
                'result' => 'search_order_by_connect_datetime.json'
            ],
            [
                'filter' => [
                    'with' => [
                        'latest_wellness',
                        'latest_reflection',
                        'latest_connect_as_employee',
                        'latest_completed_action_as_employee'
                    ],
                    'order_by' => 'latest_completed_action_as_employee.date'
                ],
                'result' => 'search_order_by_action_date.json'
            ],
            [
                'filter' => [
                    'role_id_list' => [3],
                    'include_authorized' => true
                ],
                'result' => 'get_managers_include_authorized_user.json'
            ],
        ];
    }

    /**
     * @dataProvider  getSearchFilters
     *
     * @param array $filter
     * @param string $fixture
     */
    public function testSearch($filter, $fixture)
    {
        $response = $this->actingAs($this->admin)->json('get', '/users', $filter);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture($fixture, $response->json());
    }

    public function getSearchFiltersAsManager()
    {
        return [
            [
                'filter' => ['manager_id' => 3],
                'result' => 'get_users_by_name.json'
            ],
            [
                'filter' => ['query' => 'Groot'],
                'result' => 'get_users_by_name.json'
            ],
            [
                'filter' => [
                    'with' => [
                        'latest_wellness',
                        'latest_connect_as_employee',
                    ],
                    'order_by' => 'latest_wellness.score'
                ],
                'result' => 'search_order_by_score.json'
            ],
            [
                'filter' => [
                    'with' => [
                        'character_type',
                        'avatar',
                        'department',
                        'company.package',
                        'latest_wellness',
                        'latest_reflection',
                        'latest_connect_as_employee',
                        'latest_completed_action_as_employee',
                        'manager',
                        'adaptation_map'
                    ],
                    'order_by' => 'latest_wellness.score'
                ],
                'result' => 'search_with_relations_as_manager.json'
            ],
            [
                'filter' => [
                    'for_action_as_manager' => true
                ],
                'result' => 'get_users_for_action_as_manager.json'
            ],
        ];
    }

    /**
     * @dataProvider  getSearchFiltersAsManager
     *
     * @param array $filter
     * @param string $fixture
     */
    public function testSearchAsManager($filter, $fixture)
    {
        $response = $this->actingAs($this->manager)->json('get', '/users', $filter);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture($fixture, $response->json());
    }

    public function testSearchByState()
    {
        $response = $this->actingAs($this->adminForth)->json('get', '/users', [
            'state' => 'INVITED_STATE'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture('search_by_state.json', $response->json());
    }

    public function getSearchFiltersAsEmployee()
    {
        return [
            [
                'filter' => [],
                'result' => 'get_users_as_employee.json'
            ],
            [
                'filter' => [
                    'include_manager' => true
                ],
                'result' => 'get_users_as_employee_include_manager.json'
            ],
            [
                'filter' => [
                    'for_action_as_employee' => true
                ],
                'result' => 'get_users_for_action_as_employee.json'
            ],
        ];
    }

    /**
     * @dataProvider  getSearchFiltersAsEmployee
     *
     * @param array $filter
     * @param string $fixture
     */
    public function testSearchAsEmployee($filter, $fixture)
    {
        $response = $this->actingAs($this->employee)->json('get', '/users', $filter);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture($fixture, $response->json());
    }

    public function testGetSoftDeletedUser()
    {
        $response = $this->actingAs($this->admin)->json('get', '/users/6');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture('get_deleted_user.json', $response->json());
    }

    public function testRestoreSoftDeletedUser()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/6/restore');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('users', [
            'id' => 6,
            'deleted_at' => null
        ]);
    }

    public function testRestoreNotDeletedUser()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/4/restore');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRestoreNotExistsUser()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/0/restore');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGetAsInvitedStateAndNotPassedTest()
    {
        $this->mockHttpRequest();

        $response = $this->actingAs($this->adminSecond)->json('get', '/users/5');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testGetAsInvitedStateAndPassedTest()
    {
        $this->mockHttpRequestAsPassedTest();

        $response = $this->actingAs($this->adminThird)->json('get', '/users/5');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('users', $this->getJsonFixture('user_after_get_report.json'));
    }

    public function testGetWithRelations()
    {
        $response = $this->actingAs($this->manager)->json('get', '/users/4', [
            'with' => [
                'character_type',
                'avatar',
                'department',
                'company.package',
                'latest_wellness',
                'latest_reflection',
                'latest_connect_as_employee',
                'latest_completed_action_as_employee',
                'manager',
                'adaptation_map'
            ]
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture('get_user_with_relations.json', $response->json());
    }

    public function testUpdateWithManager()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/4', [
            'id' => 4,
            'manager_id' => 2,
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 4,
            'manager_id' => 2,
        ]);
    }

    public function testBlockUser()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/4', [
            'state' => 'BLOCKED_STATE',
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 4,
            'state' => 'BLOCKED_STATE',
        ]);
    }

    public function testBlockUserAsSuperAdmin()
    {
        $response = $this->actingAs($this->superAdmin)->json('put', '/users/4', [
            'state' => 'BLOCKED_STATE',
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 4,
            'state' => 'BLOCKED_STATE',
        ]);
    }

    public function testUnblockUser()
    {
        $response = $this->actingAs($this->adminForth)->json('put', '/users/11', [
            'state' => 'ACTIVE_STATE',
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 11,
            'state' => 'ACTIVE_STATE',
        ]);
    }

    public function testUnblockUserAsSuperAdmin()
    {
        $response = $this->actingAs($this->adminForth)->json('put', '/users/11', [
            'state' => 'ACTIVE_STATE',
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => 11,
            'state' => 'ACTIVE_STATE',
        ]);
    }

    public function testBlockUserAsOtherRole()
    {
        $response = $this->actingAs($this->manager)->json('put', '/users/4', [
            'state' => 'BLOCKED_STATE',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testUnblockUserAsOtherRole()
    {
        $response = $this->actingAs($this->manager)->json('put', '/users/11', [
            'state' => 'ACTIVE_STATE',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testGetProfileAsBlockedUser()
    {
        $response = $this->actingAs($this->employeeBlocked)->json('get', '/profile');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testChangeActiveStateToInvited()
    {
        $response = $this->actingAs($this->admin)->json('put', '/users/3', [
            'state' => 'INVITED_STATE',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testChangeInvitedStateToActive()
    {
        $response = $this->actingAs($this->adminForth)->json('put', '/users/7', [
            'state' => 'ACTIVE_STATE',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUnblockFormerInvitedStateToActive()
    {
        $response = $this->actingAs($this->adminForth)->json('put', '/users/12', [
            'state' => 'ACTIVE_STATE',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUnblockFormerActiveStateToInvited()
    {
        $response = $this->actingAs($this->adminForth)->json('put', '/users/11', [
            'state' => 'INVITED_STATE',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchIncludeManagerDoesNotExists()
    {
        $response = $this->actingAs($this->admin)->json('get', '/users', [
            'include_manager' => true
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testGetAsOwnUser()
    {
        $response = $this->actingAs($this->manager)->json('get', '/users/3');

        $response->assertStatus(Response::HTTP_OK);
    }
}
