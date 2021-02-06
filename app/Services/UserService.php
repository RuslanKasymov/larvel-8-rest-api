<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    protected $repository;

    public function __construct()
    {
        $this->repository = app(UserRepository::class);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function first($options, array $with = [])
    {
        return $this->repository->first($options, $with);
    }

    public function update(int $id, array $data)
    {
        $this->repository->update($id, $data);
    }
}
