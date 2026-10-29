<?php

namespace Tests\Feature\Admin;

use App\Models\PageContent;
use App\Models\User;
use Database\Seeders\PageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
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
        $page = $this->page('tentang-kami');

        $this->actingAs($agen)->get('/admin/pages')->assertForbidden();
        $this->actingAs($agen)->get("/admin/pages/{$page->id}/edit")->assertForbidden();
        $this->actingAs($agen)->put("/admin/pages/{$page->id}", $this->payload('tentang-kami'))->assertForbidden();
    }

    public function test_admin_sees_every_page_in_the_skeleton(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/pages')
            ->assertOk()
            ->assertSee('Beranda')
            ->assertSee('Tentang Kami')
            ->assertSee('Kontak')
            ->assertSee('Syarat &amp; Ketentuan', false);
    }

    public function test_saved_copy_is_sanitised_before_it_reaches_the_database(): void
    {
        $page = $this->page('tentang-kami');

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('tentang-kami', [
                'sections.cerita.body' => '<h1>Judul</h1><ol><li data-list="bullet">Satu</li></ol><script>alert(1)</script><p style="color:red">Dua</p>',
            ]))
            ->assertRedirect(route('admin.pages.edit', $page));

        $this->assertDatabaseHas('page_sections', [
            'page_content_id' => $page->id,
            'key' => 'cerita',
            'body' => '<h2>Judul</h2><ul><li>Satu</li></ul><p>Dua</p>',
        ]);
    }

    public function test_sections_are_saved_in_the_order_the_skeleton_defines(): void
    {
        $page = $this->page('tentang-kami');

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('tentang-kami'));

        $this->assertSame(
            ['hero', 'cerita', 'nilai', 'cta'],
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
        $page = $this->page('tentang-kami');

        $content = $this->actingAs($this->admin())
            ->get("/admin/pages/{$page->id}/edit")
            ->assertOk()
            ->getContent();

        // Hero has two buttons, the closing section one; cerita and nilai none.
        $this->assertSame(2, substr_count($content, 'Teks tombol utama (opsional)'));
        $this->assertSame(1, substr_count($content, 'Teks tombol kedua (opsional)'));
    }

    public function test_a_button_target_must_be_a_web_address(): void
    {        $page = $this->page('tentang-kami');

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", $this->payload('tentang-kami', [
                'sections.cta.cta_label' => 'Klik',
                'sections.cta.cta_url' => 'javascript:alert(1)',
            ]))
            ->assertSessionHasErrors('sections.cta.cta_url');
    }

    public function test_a_page_outside_the_skeleton_is_not_found(): void
    {
        $stray = PageContent::create(['key' => 'halaman-lain', 'title' => 'Lain']);

        $this->actingAs($this->admin())->get("/admin/pages/{$stray->id}/edit")->assertNotFound();
    }

    private function page(string $key): PageContent
    {
        return PageContent::where('key', $key)->firstOrFail();
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
