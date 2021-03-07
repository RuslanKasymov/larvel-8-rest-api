<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Media\CreateMediaRequest;
use App\Http\Requests\Media\DeleteMediaRequest;
use App\Http\Requests\Media\SearchMediaRequest;

class MediaController extends Controller
{
    public function create(CreateMediaRequest $request, MediaService $service)
    {
        $file = $request->file('file');
        $data = $request->all();

        $media = $service->create($file, $data);

        return response($media, Response::HTTP_CREATED);
    }

    public function list(SearchMediaRequest $request, MediaService $service)
    {
        $result = $service->list($request->validated());

        return response($result, Response::HTTP_OK);
    }

    public function delete(DeleteMediaRequest $request, MediaService $service, $id)
    {
        $service->delete($id);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
