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

    /** Quality floor when a file has to be squeezed under the size cap. */
    public const MIN_QUALITY = 50;

    /** Quality step while squeezing, so the best quality that fits is kept. */
    public const QUALITY_STEP = 4;

    /** Matches the upload rule: nothing larger than this is stored. */
    public const MAX_BYTES = 5 * 1024 * 1024;

    /**
     * Decoding costs about 4 bytes per pixel, so a 12000x9000 phone shot would
     * need ~430 MB and kill the request. The header says enough to refuse it.
     */
    public const MAX_INPUT_EDGE = 8000;

    /**
     * Returns the stored path. A file that cannot be decoded — or is too large
     * to decode safely — is stored as it arrived, so an upload never fails the
     * request.
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

        $size = @getimagesize($path);

        if ($size === false || max($size[0], $size[1]) > self::MAX_INPUT_EDGE) {
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

        $webp = $this->encode($image);

        imagedestroy($image);

        return $webp;
    }

    /**
     * The best quality that still fits the cap: the normal quality first, then
     * small steps down, so only a file that is genuinely too big is degraded.
     * Null means "nothing fit", and the caller keeps the original upload.
     */
    private function encode(GdImage $image): ?string
    {
        for ($quality = self::QUALITY; $quality >= self::MIN_QUALITY; $quality -= self::QUALITY_STEP) {
            $bytes = $this->webp($image, $quality);

            if ($bytes === null) {
                return null;
            }

            if (strlen($bytes) <= self::MAX_BYTES) {
                return $bytes;
            }
        }

        return null;
    }

    /**
     * Encoded straight into a buffer; a temp file would have to be written and
     * read once per attempt.
     */
    private function webp(GdImage $image, int $quality): ?string
    {
        ob_start();
        $written = @imagewebp($image, null, $quality);
        $bytes = ob_get_clean();

        return $written && is_string($bytes) && $bytes !== '' ? $bytes : null;
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
