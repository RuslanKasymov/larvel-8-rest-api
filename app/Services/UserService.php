<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct()
    {
        parent::__construct();

        $this->setRepository(UserRepository::class);
    }

    public function create($data)
    {
        $this->checkAndHashPassword($data);

        return $this->repository->create($data);
    }

    public function update($options, $data): void
    {
        $this->checkAndHashPassword($data);

        $this->repository->update($options, $data);
    }

    private function checkAndHashPassword(&$data): void
    {
        if (Arr::has($data, 'password')) {
            $data['password'] = Hash::make(Arr::get($data, 'password'));
        }
    }
}
