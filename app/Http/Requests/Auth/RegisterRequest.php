<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'regex:/(?=.{15,})|((?=.*[a-zA-Z])(?=.*[0-9])(?=.{8,}))/',
                'same:password_confirmation',
            ],
            'password_confirmation' => 'required|string',
        ];
    }
}
