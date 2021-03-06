<?php

namespace App\Http\Requests\Users;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class ListUserRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->role_id == Role::ADMIN;
    }

    public function rules()
    {
        return [
            'role_id' => 'integer|nullable',
            'with' => 'array|nullable',
            'with.*' => 'string',
            'with_count' => 'array|nullable',
            'with_count.*' => 'string',
            'query' => 'string|nullable',
            'page' => 'integer|nullable',
            'per_page' => 'integer|nullable',
            'order_by' => 'string|nullable',
            'desc' => 'boolean|nullable',
            'all' => 'integer|nullable',
        ];
    }
}
