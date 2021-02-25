<?php

namespace App\Http\Requests\Media;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\MediaService;

class DeleteMediaRequest extends FormRequest
{
    protected $mediaService;

    public function init()
    {
        $this->mediaService = app(MediaService::class);
    }

    public function authorize()
    {
        return $this->isAdmin() || $this->isOwnerImage();
    }

    public function rules()
    {
        return [];
    }

    public function validateResolved()
    {
        $this->init();

        parent::validateResolved();

        if (!$this->mediaService->exists($this->route('id'))) {
            throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => 'Media']));
        }
    }

    protected function isAdmin(): bool
    {
        return $this->user()->role_id === Role::ADMIN;
    }

    private function isOwnerImage(): bool
    {
        $media = $this->mediaService->first($this->route('id'));

        return $media['owner_id'] === $this->user()->id;
    }
}
