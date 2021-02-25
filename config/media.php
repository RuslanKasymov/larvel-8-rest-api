<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed maximum file size for upload, kilobytes
    |--------------------------------------------------------------------------
    |
    | This value is shows maximum file size when file upload.
    | Using in validation.
    |
    */

    'allowed_max_size_to_upload' => env('MEDIA_ALLOWED_MAX_SIZE_TO_UPLOAD', 5120),

    'permitted_media_types' => ['jpg', 'jpeg', 'bmp', 'png'],
    'permitted_image_types' => ['jpg', 'jpeg', 'bmp', 'png']

];
