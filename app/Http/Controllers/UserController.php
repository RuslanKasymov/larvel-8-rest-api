<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Http\Requests\Users\GetUserRequest;
use App\Http\Requests\Users\CreateUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Http\Requests\Users\SearchUserRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Users\UpdateProfileRequest;
use App\Http\Requests\Users\GetUserProfileRequest;

class UserController extends Controller
{
    public function create(CreateUserRequest $request, UserService $service)
    {
        $result = $service->create($request->validated());

        return response()->json($result);
    }

    public function get(GetUserRequest $request, UserService $service, $id)
    {
        $result = $service->find($id);

        return response()->json($result);
    }

    public function update(UpdateUserRequest $request, UserService $service, User $user)
    {
        $service->update($user->id, $request->except('password'));

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function profile(GetUserProfileRequest $request, UserService $service)
    {
        $result = $service->first(
            $request->user()->id,
            $request->input('with', [])
        );

        return response()->json($result);
    }

    public function updateProfile(UpdateProfileRequest $request, UserService $service)
    {
        $service->update($request->user()->id, $request->all());

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function delete(DeleteUserRequest $request, UserService $service, $id)
    {
        $service->delete($id);

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function list(SearchUserRequest $request, UserService $service)
    {
        $result = $service->list($request->all());

        return response()->json($result);
    }
}
