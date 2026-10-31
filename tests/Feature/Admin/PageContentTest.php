<?php

namespace Tests\Feature\Admin;

use App\Models\HomeGalleryPhoto;
use App\Models\Listing;
use App\Models\PageContent;
use App\Models\User;
use Database\Seeders\PageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PageContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
        $this->seed(PageContentSeeder::class);
    }

    public function test_agen_cannot_open_or_save_pages(): void
    {
        $agen = $this->agen();
        $page = $this->page('kontak');

        $this->actingAs($agen)->get('/admin/pages')->assertForbidden();
        $this->actingAs($agen)->get("/admin/pages/{$page->id}/edit")->assertForbidden();
        $this->actingAs($agen)->put("/admin/pages/{$page->id}", $this->payload('kontak'))->assertForbidden();
    }

    public function test_admin_sees_every_page_in_the_skeleton(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/pages')
            ->assertOk()
            ->assertSee('Beranda')
            ->assertSee('Kontak')
            ->assertSee('Syarat &amp; Ketentuan', false);
    }

    public function test_saved_copy_is_sanitised_before_it_reaches_the_database(): void
    {
        $page = $this->page('kontak');

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('kontak', [
                'sections.kontak.body' => '<h1>Judul</h1><ol><li data-list="bullet">Satu</li></ol><script>alert(1)</script><p style="color:red">Dua</p>',
            ]))
            ->assertRedirect(route('admin.pages.edit', $page));

        $this->assertDatabaseHas('page_sections', [
            'page_content_id' => $page->id,
            'key' => 'kontak',
            'body' => '<h2>Judul</h2><ul><li>Satu</li></ul><p>Dua</p>',
        ]);
    }

    public function test_sections_are_saved_in_the_order_the_skeleton_defines(): void
    {
        $page = $this->page('kontak');

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('kontak'));

        $this->assertSame(
            ['kontak'],
            $page->sections()->orderBy('sort_order')->pluck('key')->all(),
        );
    }

    public function test_a_hero_image_is_stored_and_needs_alt_text(): void
    {
        $page = $this->page('home');

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('home', [
                'sections.hero.image' => UploadedFile::fake()->create('hero.jpg', 120, 'image/jpeg'),
                'sections.hero.image_alt' => '',
            ]))
            ->assertSessionHasErrors('sections.hero.image_alt');

        $response = $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('home', [
                'sections.hero.image' => UploadedFile::fake()->create('hero.jpg', 120, 'image/jpeg'),
                'sections.hero.image_alt' => 'Fasad rumah contoh',
            ]));

        $response->assertSessionHasNoErrors();

        $section = $page->sections()->where('key', 'hero')->firstOrFail();

        $this->assertNotNull($section->image);
        $this->assertSame('Fasad rumah contoh', $section->image_alt);
        Storage::disk('uploads')->assertExists($section->image);
    }

    public function test_removing_the_image_deletes_the_file(): void
    {
        $page = $this->page('home');
        $section = $page->sections()->where('key', 'hero')->firstOrFail();

        Storage::disk('uploads')->put('images/pages/hero.jpg', 'bytes');
        $section->update(['image' => 'images/pages/hero.jpg', 'image_alt' => 'Foto lama']);

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('home', [
                'sections.hero.remove_image' => '1',
                'sections.hero.image_alt' => 'Foto lama',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertNull($section->fresh()->image);
        Storage::disk('uploads')->assertMissing('images/pages/hero.jpg');
    }

    public function test_the_hero_carries_two_editable_buttons(): void
    {
        $page = $this->page('home');

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('home', [
                'sections.hero.cta_label' => 'Cari Rumah',
                'sections.hero.cta_url' => '/properti',
                'sections.hero.cta_label_2' => 'Tanya Dulu',
                'sections.hero.cta_url_2' => '/kontak',
            ]))
            ->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('Cari Rumah')
            ->assertSee('href="/properti"', false)
            ->assertSee('Tanya Dulu')
            ->assertSee('href="/kontak"', false);
    }

    public function test_a_button_without_a_target_is_not_rendered(): void
    {
        $page = $this->page('home');

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('home', [
                'sections.hero.cta_label' => 'Tombol Tanpa Tujuan',
                'sections.hero.cta_url' => '',
            ]))
            ->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Tombol Tanpa Tujuan');
    }

    public function test_button_fields_follow_the_skeleton(): void
    {
        $home = $this->page('home');

        $content = $this->actingAs($this->admin())
            ->get("/admin/pages/{$home->id}/edit")
            ->assertOk()
            ->getContent();

        // The hero is the only section with buttons.
        $this->assertSame(1, substr_count($content, 'Teks tombol utama (opsional)'));
        $this->assertSame(1, substr_count($content, 'Teks tombol kedua (opsional)'));

        $contact = $this->page('kontak');

        $content = $this->actingAs($this->admin())
            ->get("/admin/pages/{$contact->id}/edit")
            ->assertOk()
            ->getContent();

        $this->assertSame(0, substr_count($content, 'Teks tombol utama (opsional)'));
    }

    public function test_a_button_target_must_be_a_web_address(): void
    {
        $page = $this->page('home');

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('home', [
                'sections.hero.cta_label' => 'Klik',
                'sections.hero.cta_url' => 'javascript:alert(1)',
            ]))
            ->assertSessionHasErrors('sections.hero.cta_url');
    }

    public function test_a_page_outside_the_skeleton_is_not_found(): void
    {
        $stray = PageContent::create(['key' => 'halaman-lain', 'title' => 'Lain']);

        $this->actingAs($this->admin())->get("/admin/pages/{$stray->id}/edit")->assertNotFound();
    }

    public function test_the_admin_picks_the_home_gallery_photos(): void
    {
        $listing = Listing::create([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Galeri',
            'slug' => 'rumah-galeri',
            'description' => 'Deskripsi.',
            'listing_type' => 'primary',
            'property_type' => 'rumah',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ]);

        $first = $listing->images()->create(['path' => 'images/listings/satu.webp', 'alt' => 'Depan', 'sort_order' => 0]);
        $second = $listing->images()->create(['path' => 'images/listings/dua.webp', 'alt' => 'Dapur', 'sort_order' => 1]);

        $page = $this->page('home');

        $this->actingAs($this->admin())
            ->get("/admin/pages/{$page->id}/edit")
            ->assertOk()
            ->assertSee('Galeri beranda')
            ->assertSee('Cari foto properti')
            ->assertSee('id="gallery-search"', false)
            ->assertSee('Rumah Galeri')
            ->assertSee('images/listings/dua.webp', false);

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('home', ['gallery' => [$second->id, $first->id]]))
            ->assertRedirect();

        $this->assertSame(
            [$second->id, $first->id],
            HomeGalleryPhoto::orderBy('sort_order')->pluck('listing_image_id')->all(),
        );

        // Unchecking everything clears the selection and the hero falls back.
        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('home', ['gallery' => []]))
            ->assertRedirect();

        $this->assertSame(0, HomeGalleryPhoto::count());
    }

    public function test_the_gallery_picker_shows_one_property_per_page(): void
    {
        $older = $this->listingWithPhotos('Rumah Lama');
        $newer = $this->listingWithPhotos('Rumah Baru');

        $page = $this->page('home');

        $this->actingAs($this->admin())
            ->get("/admin/pages/{$page->id}/edit")
            ->assertOk()
            ->assertSee('Properti 1 dari 2')
            ->assertDontSee('Sebelumnya')
            ->assertSee('aria-label="Berikutnya"', false)
            ->assertSee('data-limit-warning', false)
            ->assertSee($newer->title)
            ->assertDontSee($older->title);

        $this->actingAs($this->admin())
            ->get("/admin/pages/{$page->id}/edit?galeri_page=2")
            ->assertOk()
            ->assertSee('Properti 2 dari 2')
            ->assertSee('aria-label="Sebelumnya"', false)
            // Last page: the next arrow is there but inert, with no label.
            ->assertDontSee('aria-label="Berikutnya"', false)
            ->assertSee($older->title)
            ->assertDontSee($newer->title);
    }

    public function test_a_pick_carried_in_the_url_survives_the_next_page(): void
    {
        $older = $this->listingWithPhotos('Rumah Lama');
        $this->listingWithPhotos('Rumah Baru');
        $photo = $older->images()->first();

        $page = $this->page('home');

        // Page 1 shows the newer listing; the older pick rides along as a field.
        $this->actingAs($this->admin())
            ->get("/admin/pages/{$page->id}/edit?pilih={$photo->id}")
            ->assertOk()
            ->assertSee('<input type="hidden" name="gallery[]" value="'.$photo->id.'">', false)
            // The pager keeps the query string, so the pick survives even
            // before the script adds the boxes ticked on this page.
            ->assertSee('pilih='.$photo->id, false);
    }

    public function test_saved_picks_are_kept_until_the_url_says_they_are_cleared(): void
    {
        $older = $this->listingWithPhotos('Rumah Lama');
        $this->listingWithPhotos('Rumah Baru');
        $photo = $older->images()->first();
        HomeGalleryPhoto::create(['listing_image_id' => $photo->id, 'sort_order' => 0]);

        $page = $this->page('home');

        $this->actingAs($this->admin())
            ->get("/admin/pages/{$page->id}/edit")
            ->assertOk()
            ->assertSee('<input type="hidden" name="gallery[]" value="'.$photo->id.'">', false);

        // An empty `pilih` means the admin cleared the selection on purpose.
        $this->actingAs($this->admin())
            ->get("/admin/pages/{$page->id}/edit?pilih=")
            ->assertOk()
            ->assertDontSee('<input type="hidden" name="gallery[]"', false);
    }

    public function test_the_gallery_is_capped_at_six_photos(): void
    {
        $listing = $this->listingWithPhotos('Rumah Galeri', 7);
        $ids = $listing->images()->pluck('id')->all();
        $page = $this->page('home');

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('home', ['gallery' => $ids]))
            ->assertSessionHasErrors('gallery');

        $this->assertSame(0, HomeGalleryPhoto::count());

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('home', ['gallery' => array_slice($ids, 0, 6)]))
            ->assertSessionHasNoErrors();

        $this->assertSame(6, HomeGalleryPhoto::count());
    }

    public function test_the_selected_photos_are_listed_above_the_counter(): void
    {
        $older = $this->listingWithPhotos('Rumah Lama');
        $newer = $this->listingWithPhotos('Rumah Baru');

        // Page 1 is the newer property, so the older pick is a carried one.
        $picked = $older->images()->first();
        $unpicked = $newer->images()->first();
        HomeGalleryPhoto::create(['listing_image_id' => $picked->id, 'sort_order' => 0]);

        $page = $this->page('home');

        $this->actingAs($this->admin())
            ->get("/admin/pages/{$page->id}/edit")
            ->assertOk()
            ->assertSee('Foto yang dipakai sekarang')
            // A pick from another page shows up straight away, with a way out.
            ->assertSee('data-picked-id="'.$picked->id.'"', false)
            ->assertSee("unpick(\$event, '".$picked->id."')", false)
            // Photos of this page carry a chip too, hidden until ticked.
            ->assertSee('data-picked-id="'.$unpicked->id.'"', false);
    }

    private function page(string $key): PageContent
    {
        return PageContent::where('key', $key)->firstOrFail();
    }

    private function listingWithPhotos(string $title, int $photos = 2): Listing
    {
        $listing = Listing::create([
            'location_id' => $this->location()->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => 'Deskripsi.',
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ]);

        foreach (range(1, $photos) as $index) {
            $listing->images()->create([
                'path' => 'images/listings/'.Str::slug($title).'-'.$index.'.webp',
                'alt' => $title.' foto '.$index,
                'sort_order' => $index,
            ]);
        }

        return $listing;
    }

    private function payload(string $key, array $overrides = []): array
    {
        $sections = [];

        foreach (array_keys(config("pages.$key.sections")) as $sectionKey) {
            $sections[$sectionKey] = [
                'heading' => 'Bagian '.$sectionKey,
                'body' => '<p>Isi '.$sectionKey.'</p>',
                'cta_label' => null,
                'cta_url' => null,
            ];
        }

        $payload = ['title' => 'Judul', 'sections' => $sections];

        foreach ($overrides as $path => $value) {
            Arr::set($payload, $path, $value);
        }

        return $payload;
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function agen(): User
    {
        return User::factory()->create(['role' => User::ROLE_AGEN, 'phone' => '089630999996']);
    }
}
