<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(User::class);
    }

    public function list($filters)
    {
        return $this
            ->listQuery($filters)
            ->filterBy('role_id')
            ->filterByQuery(['name', 'email'])
            ->getResults();
    }
}
