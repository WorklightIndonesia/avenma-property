<?php

namespace Tests\Feature\Admin;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListingPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
    }

    public function test_photos_are_stored_with_the_listing(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload([
                'photos' => [
                    UploadedFile::fake()->create('depan.jpg', 120, 'image/jpeg'),
                    UploadedFile::fake()->create('dapur.webp', 90, 'image/webp'),
                ],
            ]))
            ->assertRedirect();

        $listing = Listing::firstOrFail();
        $images = $listing->images;

        $this->assertCount(2, $images);
        $this->assertSame([1, 2], $images->pluck('sort_order')->all());

        foreach ($images as $image) {
            $this->assertStringStartsWith('images/listings/'.$listing->id.'/', $image->path);
            Storage::disk('uploads')->assertExists($image->path);
        }
    }

    public function test_labels_and_alt_are_saved_from_the_edit_form(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id]);
        $image = $listing->images()->create(['path' => 'images/properties/foto-1.webp']);

        $this->actingAs($agen)
            ->put(route('admin.listings.update', $listing), $this->payload([
                'images' => [$image->id => ['label' => 'Tampak depan', 'alt' => 'Rumah dua lantai', 'sort_order' => 3]],
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('listing_images', [
            'id' => $image->id,
            'label' => 'Tampak depan',
            'alt' => 'Rumah dua lantai',
            'sort_order' => 3,
        ]);
    }

    public function test_labels_of_another_listing_are_left_alone(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id]);

        $other = $this->listing(['created_by' => $this->agen()->id]);
        $otherImage = $other->images()->create(['path' => 'images/properties/foto-lain.webp']);

        $this->actingAs($agen)
            ->put(route('admin.listings.update', $listing), $this->payload([
                'images' => [$otherImage->id => ['label' => 'Dibajak']],
            ]))
            ->assertRedirect();

        $this->assertNull($otherImage->refresh()->label);
    }

    public function test_a_photo_can_be_deleted_with_its_file(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id]);

        Storage::disk('uploads')->put('images/listings/1/foto.jpg', 'fake bytes');
        $image = $listing->images()->create(['path' => 'images/listings/1/foto.jpg']);

        $this->actingAs($agen)
            ->delete(route('admin.listings.photos.destroy', [$listing, $image]))
            ->assertRedirect();

        $this->assertModelMissing($image);
        Storage::disk('uploads')->assertMissing('images/listings/1/foto.jpg');
    }

    public function test_a_photo_of_another_listing_cannot_be_deleted_through_the_wrong_one(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id]);
        $other = $this->listing(['created_by' => $agen->id]);
        $image = $other->images()->create(['path' => 'images/properties/foto-lain.webp']);

        $this->actingAs($agen)
            ->delete(route('admin.listings.photos.destroy', [$listing, $image]))
            ->assertNotFound();

        $this->assertModelExists($image);
    }

    public function test_agen_cannot_delete_a_photo_on_another_agens_listing(): void
    {
        $listing = $this->listing(['created_by' => $this->agen()->id]);
        $image = $listing->images()->create(['path' => 'images/properties/foto-1.webp']);

        $this->actingAs($this->agen())
            ->delete(route('admin.listings.photos.destroy', [$listing, $image]))
            ->assertForbidden();

        $this->assertModelExists($image);
    }

    public function test_a_non_image_upload_is_rejected(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload([
                'photos' => [UploadedFile::fake()->create('shell.php', 10, 'application/x-php')],
            ]))
            ->assertSessionHasErrors('photos.0');

        $this->assertDatabaseCount('listings', 0);
    }

    private function agen(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => User::ROLE_AGEN], $attributes));
    }

    private function listing(array $attributes = []): Listing
    {
        return Listing::create(array_merge([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => Str::random(10),
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ], $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $attributes = []): array
    {
        return array_merge([
            'title' => 'Rumah Uji',
            'description' => 'Deskripsi uji.',
            'location_id' => $this->location()->id,
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => '1.250.000.000',
            'latitude' => '-6.2615',
            'longitude' => '106.8',
            'is_published' => '1',
        ], $attributes);
    }
}
