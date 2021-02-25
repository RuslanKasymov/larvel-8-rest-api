<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class CreateMediaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $allowedMaxSize = config('media.allowed_max_size_to_upload');
        $types = implode(',', config('media.permitted_media_types'));

        return [
            'is_public' => 'boolean',
            'file' => "file|required|max:{$allowedMaxSize}|mimes:{$types}",
        ];
    }
}
