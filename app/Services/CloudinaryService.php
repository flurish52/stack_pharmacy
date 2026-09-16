<?php

namespace App\Services;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
        ]);
    }

    public function upload(string $filePath, string $folder = 'stack-pharmacy/products'): string
    {
        $result = $this->cloudinary->uploadApi()->upload($filePath, [
            'folder' => $folder,
        ]);

        return $result['public_id'];
    }

    public function destroy(string $publicId): void
    {
        $this->cloudinary->uploadApi()->destroy($publicId);
    }

    public function url(string $publicId, int $width = 600, int $height = 600): string
    {
        $cloudName = config('services.cloudinary.cloud_name');

        return "https://res.cloudinary.com/{$cloudName}/image/upload/w_{$width},h_{$height},c_fill,q_auto,f_auto/{$publicId}";
    }
}
