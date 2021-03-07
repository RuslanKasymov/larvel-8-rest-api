<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Storage;

trait MediaTestTrait
{
    protected function clearUploadedFilesFolder()
    {
        $files = Storage::allFiles();

        foreach ($files as $file) {
            Storage::delete($file);
        }
    }
}
