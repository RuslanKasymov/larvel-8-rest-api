<?php

namespace App\Services;

use App\Repositories\MediaRepository;
use App\Support\Traits\FileProcessingTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

/**
 * @property  MediaRepository $repository
 */
class MediaService extends BaseService
{
    use FileProcessingTrait;

    public function __construct()
    {
        parent::__construct();

        $this->setRepository(MediaRepository::class);
    }

    public function create($file, $data = [], $folder = '')
    {
        $data['name'] = $file->getClientOriginalName();
        $data['owner_id'] = $this->authorizedUser->id;

        if ($this->isPermittedImage($file)) {
            $this->rotateFromExif($file);
        }

        $options = (Arr::get($data, 'is_public') == true) ? 'public' : [];

        list($data['link'], $data['filepath']) = $this->saveFile($file, $folder, $options);

        return $this->repository->create($data);
    }

    public function delete($where)
    {
        $file = $this->repository->first($where);

        Storage::delete($file['filepath']);

        $this->repository->delete($where);
    }
}
