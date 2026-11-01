<?php

namespace App\Console\Commands;

use App\Models\ListingImage;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Writes the 480px and 960px variants beside every stored listing image.
 * New uploads get them from ImageService::store(); this covers the rows that
 * were uploaded before the variants existed. Safe to run more than once.
 */
class GenerateThumbnails extends Command
{
    protected $signature = 'images:variants {--force : Rebuild variants that already exist}';

    protected $description = 'Write the thumbnail and medium variants beside every stored listing image.';

    public function handle(ImageService $images): int
    {
        $disk = 'uploads';
        $storage = Storage::disk($disk);
        $written = 0;
        $skipped = 0;
        $failed = 0;

        ListingImage::query()
            ->select(['id', 'path'])
            ->cursor()
            ->each(function (ListingImage $image) use ($images, $storage, $disk, &$written, &$skipped, &$failed): void {
                $path = (string) $image->path;

                if ($path === '' || ! $storage->exists($path)) {
                    $failed++;

                    return;
                }

                $exists = $storage->exists(ImageService::thumbPath($path))
                    && $storage->exists(ImageService::mediumPath($path));

                if ($exists && ! $this->option('force')) {
                    $skipped++;

                    return;
                }

                if ($images->writeVariantsFor($path, $disk)) {
                    $written++;
                } else {
                    $failed++;
                }
            });

        $this->info("variants written={$written} skipped={$skipped} failed={$failed}");

        if ($failed > 0) {
            $this->warn('Files that could not be decoded keep serving their original; see the log for the paths.');
        }

        return self::SUCCESS;
    }
}
