<?php

namespace App\Services;

use BadMethodCallException;

abstract class Service
{
    protected $authorizedUser;
    protected $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
        $this->authorizedUser = auth()->user();
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
