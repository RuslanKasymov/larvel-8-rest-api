<?php

namespace App\Repositories;

use App\Models\Media;

/**
 * @property  Media $model
 */
class MediaRepository extends Repository
{
    public function __construct(Media $model)
    {
        parent::__construct($model);
    }

    public function list($filters)
    {
        return $this
            ->listQuery($filters)
            ->filterByQuery(['name'])
            ->getResults();
    }

    public function getSearchResults()
    {
        $this->query->getResults();

        return parent::getResults();
    }
}
