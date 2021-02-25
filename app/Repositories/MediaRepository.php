<?php

namespace App\Repositories;

use App\Models\Media;

/**
 * @property  Media $model
 */
class MediaRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(Media::class);
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
