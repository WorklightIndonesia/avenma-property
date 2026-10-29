<?php

namespace Tests\Feature\Public;

use App\Models\BlogPost;
use App\Models\Listing;
use App\Models\PageContent;
use App\Models\User;
use Database\Seeders\PageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_listing_page_carries_its_own_title_description_canonical_and_image(): void
    {
        $listing = $this->listing(['title' => 'Rumah Cluster Bintaro', 'slug' => 'rumah-cluster-bintaro']);

        $listing->images()->create([
            'path' => 'images/properties/foto-1.webp',
            'alt' => null,
            'sort_order' => 0,
        ]);

        $this->get('/properti/rumah-cluster-bintaro')
            ->assertOk()
            ->assertSee('<title>Rumah Cluster Bintaro - Avenma Properti</title>', false)
            ->assertSee('content="Deskripsi uji."', false)
            ->assertSee('<link rel="canonical" href="'.url('/properti/rumah-cluster-bintaro').'">', false)
            ->assertSee('property="og:image" content="'.asset('images/properties/foto-1.webp').'"', false);
    }

    public function test_the_catalog_canonical_drops_the_filter_query(): void
    {
        $this->get('/properti?tipe=rumah&harga=under-500')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/properti').'">', false);
    }

    public function test_a_blog_article_is_marked_as_an_article_with_its_cover(): void
    {
        $this->article([
            'title' => 'Panduan KPR',
            'slug' => 'panduan-kpr',
            'excerpt' => 'Ringkasan panduan.',
            'cover_image' => 'images/blog/sampul.webp',
        ]);

        $this->get('/blog/panduan-kpr')
            ->assertOk()
            ->assertSee('<title>Panduan KPR - Avenma Properti</title>', false)
            ->assertSee('content="Ringkasan panduan."', false)
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('property="og:image" content="'.asset('images/blog/sampul.webp').'"', false);
    }

    public function test_the_sitemap_lists_pages_and_published_content_only(): void
    {
        $this->seed(PageContentSeeder::class);

        $this->listing(['slug' => 'rumah-terbit']);
        $this->listing(['slug' => 'rumah-draf', 'is_published' => false]);
        $this->article(['slug' => 'artikel-terbit']);
        $this->article(['slug' => 'artikel-draf', 'is_published' => false]);

        $response = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $content = $response->getContent();

        $this->assertNotFalse(simplexml_load_string($content), 'Sitemap is not valid XML.');

        $this->assertStringContainsString('<loc>'.route('public.listings.show', 'rumah-terbit').'</loc>', $content);
        $this->assertStringNotContainsString('rumah-draf', $content);
        $this->assertStringContainsString('<loc>'.url('/tentang-kami').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.route('public.blog.index').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.route('public.blog.show', 'artikel-terbit').'</loc>', $content);
        $this->assertStringNotContainsString('artikel-draf', $content);
    }

    public function test_the_sitemap_skips_a_page_without_a_row(): void
    {
        $this->seed(PageContentSeeder::class);
        PageContent::where('key', 'kontak')->delete();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(url('/kontak'))
            ->assertSee(url('/tentang-kami'));
    }

    public function test_robots_points_at_the_sitemap_and_keeps_the_admin_out(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('public.sitemap'));
    }

    private function listing(array $attributes = []): Listing
    {
        return Listing::create(array_merge([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => Str::random(10),
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ], $attributes));
    }

    private function article(array $attributes = []): BlogPost
    {
        return BlogPost::create(array_merge([
            'created_by' => User::factory()->create(['role' => User::ROLE_AGEN, 'phone' => '089630999996'])->id,
            'title' => 'Artikel Uji',
            'slug' => Str::random(10),
            'excerpt' => 'Ringkasan.',
            'content' => '<p>Isi.</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ], $attributes));
    }
}
