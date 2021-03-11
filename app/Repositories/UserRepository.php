<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends Repository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
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
