<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService extends BaseService
{
    protected $repository;

    public function __construct()
    {
        $this->repository = app(UserRepository::class);
    }
}
