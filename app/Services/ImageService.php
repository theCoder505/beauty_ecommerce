<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    protected const MAX_SIZE_KB = 900;
    protected const MAX_WIDTH   = 1200;
    protected const MAX_HEIGHT  = 1200;

    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Store and compress an uploaded image.
     * Returns the storage path relative to public disk.
     */
    public function store(UploadedFile $file, string $folder = 'uploads', int $quality = 80): string
    {
        $image = $this->manager->read($file->getRealPath());

        // Resize if too large while keeping aspect ratio
        if ($image->width() > self::MAX_WIDTH || $image->height() > self::MAX_HEIGHT) {
            $image->scaleDown(self::MAX_WIDTH, self::MAX_HEIGHT);
        }

        $filename = Str::uuid() . '.webp';
        $path     = $folder . '/' . $filename;

        // Encode as WebP and compress
        $encoded = $image->toWebp($quality);

        // If still >900KB, reduce quality
        $q = $quality;
        while (strlen($encoded) > self::MAX_SIZE_KB * 1024 && $q > 20) {
            $q -= 10;
            $encoded = $image->toWebp($q);
        }

        Storage::disk('public')->put($path, $encoded);

        return $path;
    }

    /**
     * Delete an image from storage.
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Store multiple images and return paths.
     */
    public function storeMany(array $files, string $folder = 'uploads'): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->store($file, $folder);
            }
        }
        return $paths;
    }
}

