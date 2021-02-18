<?php

namespace App\Http\Requests\Users;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class SearchUserRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->role_id == Role::ADMIN;
    }

    public function rules()
    {
        return [
            'role_id' => 'integer|nullable',
            'query' => 'string|nullable',
            'page' => 'integer|nullable',
            'per_page' => 'integer|nullable',
            'order_by' => 'string|nullable',
            'desc' => 'boolean|nullable',
            'all' => 'integer|nullable',
        ];
    }
}