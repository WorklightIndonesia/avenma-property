<?php

namespace Tests\Feature\Admin;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');

        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD with WebP support is required.');
        }
    }

    public function test_a_large_upload_is_saved_as_webp_at_the_maximum_width(): void
    {
        $path = $this->images()->store($this->jpeg(3000, 2000), 'images/blog');

        $this->assertStringEndsWith('.webp', $path);

        $size = getimagesizefromstring(Storage::disk('uploads')->get($path));

        $this->assertSame(IMAGETYPE_WEBP, $size[2]);
        $this->assertSame(ImageService::MAX_WIDTH, $size[0]);
        $this->assertSame(1280, $size[1]);
    }

    public function test_an_upload_narrower_than_the_limit_keeps_its_size(): void
    {
        $path = $this->images()->store($this->jpeg(800, 600), 'images/blog');

        $size = getimagesizefromstring(Storage::disk('uploads')->get($path));

        $this->assertSame(800, $size[0]);
        $this->assertSame(600, $size[1]);
    }

    public function test_an_undecodable_upload_is_stored_unchanged(): void
    {
        $path = $this->images()->store(UploadedFile::fake()->create('foto.jpg', 20, 'image/jpeg'), 'images/blog');

        $this->assertStringEndsWith('.jpg', $path);
        Storage::disk('uploads')->assertExists($path);
    }

    public function test_a_blog_cover_is_converted_through_the_service(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))
            ->post('/admin/blog', [
                'title' => 'Artikel Bersampul',
                'excerpt' => 'Ringkasan.',
                'content' => '<p>Isi artikel.</p>',
                'is_published' => '1',
                'cover' => $this->jpeg(2400, 1600),
            ])
            ->assertSessionHasNoErrors();

        $post = BlogPost::firstOrFail();

        $this->assertStringEndsWith('.webp', $post->cover_image);

        $size = getimagesizefromstring(Storage::disk('uploads')->get($post->cover_image));

        $this->assertSame(ImageService::MAX_WIDTH, $size[0]);
        $this->assertSame(1280, $size[1]);
    }

    private function images(): ImageService
    {
        return app(ImageService::class);
    }

    private function jpeg(int $width, int $height): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, imagecolorallocate($image, 20, 120, 90));

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('img_', true).'.jpg';
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return new UploadedFile($path, 'foto.jpg', 'image/jpeg', null, true);
    }
}
