<?php

namespace App\Support\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Exception;

trait FileProcessingTrait
{
    private function saveFile($file, string $folder = '', $options = []): array
    {
        $content = file_get_contents($file->getPathname());

        $newFileName = $this->generateName($file->getClientOriginalName());
        $filePath = ($folder) ? $folder . '/' . $newFileName : $newFileName;

        return [$this->putToStorage($filePath, $content, $options), $filePath];
    }

    private function rotateFromExif(UploadedFile $file): void
    {
        try {
            $exif = exif_read_data($file);
        } catch (Exception $e) {
            return;
        }

        if (Arr::has($exif, 'Orientation')) {
            $angle = $this->getRotationAngle(Arr::get($exif, 'Orientation'));

            if($angle){
                $this->rotateImage($file, $angle);
            }
        }
    }

    private function getRotationAngle(int $orientationType): int
    {
        $map = [
            3 => 180,
            6 => 270,
            8 => 90
        ];

        return $map[$orientationType] ?? false;
    }

    private function rotateImage(UploadedFile $file, int $angle): void
    {
        $filePath = $file->getPathName();

        switch ($file->getClientOriginalExtension()) {
            case 'png':
                $imgNew = imagecreatefrompng($filePath);
                $imgNew = imagerotate($imgNew, $angle, 0);
                imagepng($imgNew, $filePath);

                break;
            case 'jpg':
            case 'jpeg':
                $imgNew = imagecreatefromjpeg($filePath);
                $imgNew = imagerotate($imgNew, $angle, 0);
                imagejpeg($imgNew, $filePath, 80);

                break;
            case 'bmp':
                $imgNew = imagecreatefrombmp($filePath);
                $imgNew = imagerotate($imgNew, $angle, 0);
                imagebmp($imgNew, $filePath, 80);

                break;
        }
    }

    private function isPermittedImage(UploadedFile $file): bool
    {
        return in_array($file->getClientOriginalExtension(), config('media.permitted_image_types'));
    }

    private function generateName($path)
    {
        $name = basename($path);
        $explodedName = explode('.', $name);
        $extension = array_pop($explodedName);
        $hash = md5(uniqid());
        $timestamp = str_replace(['.', ' '], '_', microtime());

        return "{$timestamp}_{$hash}.{$extension}";
    }

    private function putToStorage(string $filePath, $content, $options = []): string
    {
        Storage::put($filePath, $content, $options);

        return Storage::url($filePath);
    }
}
