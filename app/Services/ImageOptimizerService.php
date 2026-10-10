<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Resize and compress an uploaded image to lightweight WebP format.
     */
    public function optimizeAndStore(
        UploadedFile $file,
        string $directory = 'manufacturers/logos',
        int $maxWidth = 200,
        int $maxHeight = 200,
        int $quality = 80,
        string $disk = 'public'
    ): string {
        $mime = $file->getMimeType();

        // If SVG, store directly to preserve vector paths
        if ($mime === 'image/svg+xml') {
            return $file->store($directory, $disk);
        }

        $realPath = $file->getRealPath();
        $content = $realPath ? file_get_contents($realPath) : null;

        if (! $content) {
            return $file->store($directory, $disk);
        }

        $sourceImage = @imagecreatefromstring($content);

        if (! $sourceImage) {
            return $file->store($directory, $disk);
        }

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Keep original dimensions if smaller, otherwise scale down preserving aspect ratio
        $ratio = min($maxWidth / max($origWidth, 1), $maxHeight / max($origHeight, 1), 1.0);
        $targetWidth = max(1, (int) round($origWidth * $ratio));
        $targetHeight = max(1, (int) round($origHeight * $ratio));

        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Preserve alpha transparency for PNG / WebP / GIF
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $origWidth,
            $origHeight
        );

        $filename = Str::uuid().'.webp';
        $relativePath = trim($directory, '/').'/'.$filename;

        // Output to buffer as compressed WebP
        ob_start();
        imagewebp($targetImage, null, $quality);
        $webpData = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        Storage::disk($disk)->put($relativePath, $webpData);

        return $relativePath;
    }
}
