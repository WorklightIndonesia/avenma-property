<?php

namespace Tests\Feature\Public;

use App\Models\Listing;
use Database\Seeders\PageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HomeFeaturedTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_shows_five_listings(): void
    {
        $this->seed(PageContentSeeder::class);

        foreach (range(1, 7) as $index) {
            $this->listing(['title' => 'Rumah Nomor '.$index]);
        }

        $response = $this->get('/')->assertOk();

        $this->assertSame(5, substr_count($response->getContent(), 'Rumah Nomor'));
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

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Rumah Lama', 'Rumah Keenam', 'Rumah Kelima', 'Rumah Keempat', 'Rumah Ketiga'])
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

    private function listing(array $attributes = [], int $daysAgo = 0): Listing
    {
        $listing = Listing::create(array_merge([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => Str::random(10),
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah',
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
