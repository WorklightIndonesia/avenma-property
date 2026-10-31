<?php

namespace Tests\Feature\Public;

use App\Models\BlogPost;
use App\Models\HomeGalleryPhoto;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\PageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HomeFeaturedTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_shows_six_listings(): void
    {
        $this->seed(PageContentSeeder::class);

        foreach (range(1, 8) as $index) {
            $this->listing(['title' => 'Rumah Nomor '.$index]);
        }

        $response = $this->get('/')->assertOk();

        $this->assertSame(6, substr_count($response->getContent(), 'Rumah Nomor'));
    }

    public function test_featured_listings_come_first_even_when_older(): void
    {
        $this->seed(PageContentSeeder::class);

        $old = $this->listing(['title' => 'Rumah Lama', 'is_featured' => true], daysAgo: 20);
        $second = $this->listing(['title' => 'Rumah Kedua'], daysAgo: 10);
        $this->listing(['title' => 'Rumah Ketiga'], daysAgo: 8);
        $this->listing(['title' => 'Rumah Keempat'], daysAgo: 6);
        $this->listing(['title' => 'Rumah Kelima'], daysAgo: 4);
        $this->listing(['title' => 'Rumah Keenam'], daysAgo: 2);
        $this->listing(['title' => 'Rumah Ketujuh'], daysAgo: 1);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Rumah Lama', 'Rumah Ketujuh', 'Rumah Keenam', 'Rumah Kelima', 'Rumah Keempat', 'Rumah Ketiga'])
            ->assertDontSee('Rumah Kedua');

        $this->assertTrue($old->refresh()->is_featured);
        $this->assertFalse($second->refresh()->is_featured);
    }

    public function test_the_section_is_hidden_when_nothing_is_published(): void
    {
        $this->seed(PageContentSeeder::class);
        $this->listing(['title' => 'Rumah Draf', 'is_published' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Properti pilihan')
            ->assertDontSee('Rumah Draf');
    }

    public function test_the_hero_gallery_uses_the_photos_picked_in_the_admin(): void
    {
        $this->seed(PageContentSeeder::class);

        $featured = $this->listing(['title' => 'Rumah Unggulan', 'is_featured' => true]);
        $featured->images()->create(['path' => 'images/listings/unggulan.webp', 'alt' => 'Fasad rumah unggulan', 'sort_order' => 1]);

        $plain = $this->listing(['title' => 'Rumah Biasa']);
        $picked = $plain->images()->create(['path' => 'images/listings/biasa.webp', 'alt' => 'Fasad rumah biasa', 'sort_order' => 1]);

        HomeGalleryPhoto::create(['listing_image_id' => $picked->id, 'sort_order' => 0]);

        $hero = Str::before($this->get('/')->assertOk()->getContent(), 'Properti pilihan');

        $this->assertStringContainsString('images/listings/biasa.webp', $hero);
        $this->assertStringContainsString('href="'.route('public.listings.show', $plain).'"', $hero);
        $this->assertStringNotContainsString('images/listings/unggulan.webp', $hero);
    }

    public function test_the_home_page_lists_the_five_most_recent_articles(): void
    {
        $this->seed(PageContentSeeder::class);

        foreach (range(1, 7) as $index) {
            BlogPost::create([
                'created_by' => User::factory()->create(['role' => User::ROLE_AGEN])->id,
                'title' => 'Artikel Nomor '.$index,
                'slug' => 'artikel-nomor-'.$index,
                'excerpt' => 'Ringkasan.',
                'content' => '<p>Isi.</p>',
                'is_published' => true,
                'published_at' => now()->subDays($index),
            ]);
        }

        $response = $this->get('/')->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), 'Artikel Terkini'));
        $this->assertStringContainsString('Lihat artikel lainnya', $response->getContent());
        $this->assertSame(5, substr_count($response->getContent(), 'Artikel Nomor'));
    }

    public function test_the_home_page_carries_the_kpr_simulator(): void
    {
        $this->seed(PageContentSeeder::class);

        $this->listing(['title' => 'Rumah Simulasi', 'price' => 1_500_000_000]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Simulasi KPR')
            ->assertSee('placeholder="Masukkan harga properti"', false)
            ->assertDontSee('value="1.500.000.000"', false);
    }

    private function listing(array $attributes = [], int $daysAgo = 0): Listing
    {
        $listing = Listing::create(array_merge([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => Str::random(10),
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ], $attributes));

        $listing->forceFill([
            'created_at' => now()->subDays($daysAgo),
            'updated_at' => now()->subDays($daysAgo),
        ])->save();

        return $listing;
    }
}
