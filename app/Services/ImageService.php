<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploads as WebP at the width the site serves. Uses the GD extension
 * bundled with PHP, so shared hosting needs no extra Composer package.
 */
class ImageService
{
    /** Widest image the site serves; anything larger is scaled down. */
    public const MAX_WIDTH = 1920;

    public const QUALITY = 82;

    /**
     * Returns the stored path. A file that cannot be decoded is stored as it
     * arrived, so an upload never fails the request.
     */
    public function store(UploadedFile $file, string $directory, string $disk = 'uploads'): string
    {
        $webp = $this->toWebp($file->getRealPath());

        if ($webp === null) {
            return (string) $file->store($directory, $disk);
        }

        $path = $directory.'/'.Str::random(40).'.webp';

        Storage::disk($disk)->put($path, $webp);

        return $path;
    }

    private function toWebp(string $path): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return null;
        }

        $bytes = @file_get_contents($path);
        $image = $bytes === false ? false : @imagecreatefromstring($bytes);

        if (! $image instanceof GdImage) {
            return null;
        }

        $image = $this->applyOrientation($image, $path);

        if (imagesx($image) > self::MAX_WIDTH) {
            $scaled = imagescale($image, self::MAX_WIDTH, -1, IMG_BICUBIC);

            if ($scaled instanceof GdImage) {
                imagedestroy($image);
                $image = $scaled;
            }
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $temp = tempnam(sys_get_temp_dir(), 'webp');
        $written = $temp !== false && @imagewebp($image, $temp, self::QUALITY);

        imagedestroy($image);

        if ($temp === false) {
            return null;
        }

        $webp = $written ? file_get_contents($temp) : false;

        @unlink($temp);

        return $webp === false ? null : $webp;
    }

    /**
     * Phone photos carry the rotation in EXIF; without this, a portrait shot
     * lands sideways.
     */
    private function applyOrientation(GdImage $image, string $path): GdImage
    {
        $exif = @exif_read_data($path);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

        // shortcut: mirrored orientations (2, 4, 5, 7) are rotated, not flipped;
        // swap in imageflip when a real upload shows the wrong way round
        $rotated = match ($orientation) {
            3 => @imagerotate($image, 180, 0),
            6 => @imagerotate($image, -90, 0),
            8 => @imagerotate($image, 90, 0),
            default => false,
        };

        if (! $rotated instanceof GdImage) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }
}
