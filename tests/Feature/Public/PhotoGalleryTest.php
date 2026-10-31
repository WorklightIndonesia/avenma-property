<?php

namespace Tests\Feature\Public;

use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PhotoGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_listing_with_several_photos_offers_the_fullscreen_viewer(): void
    {
        $listing = $this->listing(['title' => 'Rumah Cluster Bintaro', 'slug' => 'rumah-cluster-bintaro']);

        $listing->images()->createMany([
            ['path' => 'images/properties/foto-1.webp', 'alt' => 'Tampak depan', 'label' => 'Tampak depan', 'sort_order' => 0],
            ['path' => 'images/properties/foto-2.webp', 'alt' => 'Dapur', 'label' => 'Dapur', 'sort_order' => 1],
        ]);

        $this->get('/properti/rumah-cluster-bintaro')
            ->assertOk()
            ->assertSee('x-data="photoGallery(', false)
            ->assertSee('Lihat semua foto')
            ->assertSee('aria-label="Tutup foto"', false)
            ->assertSee('Foto berikutnya')
            ->assertSee('Tampilkan foto 2: Dapur')
            ->assertSee('/images/properties/foto-1.webp', false);
    }

    public function test_a_single_photo_still_opens_but_has_no_carets(): void
    {
        $listing = $this->listing(['slug' => 'rumah-satu-foto']);

        $listing->images()->create([
            'path' => 'images/properties/foto-1.webp',
            'alt' => 'Tampak depan',
            'sort_order' => 0,
        ]);

        $this->get('/properti/rumah-satu-foto')
            ->assertOk()
            ->assertSee('Lihat semua foto')
            ->assertDontSee('Foto berikutnya')
            ->assertDontSee('Foto sebelumnya');
    }

    public function test_a_listing_without_photos_shows_the_placeholder_only(): void
    {
        $this->listing(['slug' => 'rumah-tanpa-foto']);

        $this->get('/properti/rumah-tanpa-foto')
            ->assertOk()
            ->assertDontSee('Lihat semua foto')
            ->assertDontSee('x-data="photoGallery(', false);
    }

    public function test_a_photo_without_alt_text_falls_back_to_the_listing_title(): void
    {
        $listing = $this->listing(['title' => 'Rumah Uji', 'slug' => 'rumah-uji-alt']);

        $listing->images()->create([
            'path' => 'images/properties/foto-1.webp',
            'alt' => null,
            'sort_order' => 0,
        ]);

        $this->get('/properti/rumah-uji-alt')
            ->assertOk()
            ->assertSee('alt="Rumah Uji di Pondok Aren"', false);
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
}
