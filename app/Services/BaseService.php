<?php

namespace App\Services;

use BadMethodCallException;

class BaseService
{
    protected $authorizedUser;
    protected $repository;

    public function __construct()
    {
        $this->authorizedUser = auth()->user();
    }

    public function setRepository($repository)
    {
        $this->repository = app($repository);

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->repository, $name)) {
            $result = call_user_func_array([$this->repository, $name], $arguments);

            if ($result === $this->repository) {
                return $this;
            }

            return $result;
        }

        throw new BadMethodCallException();
    }
}
