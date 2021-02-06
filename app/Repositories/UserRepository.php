<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = app(User::class);
    }

    public function create(array $options): User
    {
        return User::create($options);
    }

    public function first($options, array $with = []): ?User
    {
        return User::where($options)->with($with)->first();
    }

    public function update(int $id, array $data)
    {
        User::where('id', $id)->update($data);
    }

    public function findByEmail($email): ?User
    {
        return User::whereEmail($email)->first();
    }
}
