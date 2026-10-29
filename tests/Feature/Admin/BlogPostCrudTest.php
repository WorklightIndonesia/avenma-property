<?php

namespace Tests\Feature\Admin;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogPostCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
    }

    public function test_agen_writes_and_publishes_an_article(): void
    {
        $agen = $this->agen();

        $this->actingAs($agen)
            ->post('/admin/blog', $this->payload(['title' => 'Tips Beli Rumah']))
            ->assertSessionHasNoErrors();

        $post = BlogPost::firstOrFail();

        $this->assertSame('tips-beli-rumah', $post->slug);
        $this->assertSame($agen->id, $post->created_by);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    public function test_saved_content_is_sanitised(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/blog', $this->payload([
                'content' => '<h1>Judul</h1><script>alert(1)</script><p style="color:red">Isi</p>',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('<h2>Judul</h2><p>Isi</p>', BlogPost::firstOrFail()->content);
    }

    public function test_an_empty_editor_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/blog', $this->payload(['content' => '<p><br></p>']))
            ->assertSessionHasErrors('content');

        $this->assertSame(0, BlogPost::count());
    }

    public function test_a_draft_stays_off_the_site_until_published(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/blog', $this->payload(['is_published' => null]))
            ->assertSessionHasNoErrors();

        $post = BlogPost::firstOrFail();

        $this->assertFalse($post->is_published);
        $this->assertNull($post->published_at);
        $this->get('/blog/'.$post->slug)->assertNotFound();
    }

    public function test_a_slug_never_repeats(): void
    {
        $this->article(['title' => 'Artikel Sama', 'slug' => 'artikel-sama']);

        $this->actingAs($this->admin())
            ->post('/admin/blog', $this->payload(['title' => 'Artikel Sama']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('blog_posts', ['slug' => 'artikel-sama-2']);
    }

    public function test_agen_sees_and_edits_only_their_own_articles(): void
    {
        $agen = $this->agen();
        $other = $this->agen();

        $mine = $this->article(['created_by' => $agen->id, 'title' => 'Milik Saya', 'slug' => 'milik-saya']);
        $theirs = $this->article(['created_by' => $other->id, 'title' => 'Milik Agen Lain', 'slug' => 'milik-agen-lain']);

        $this->actingAs($agen)->get('/admin/blog')
            ->assertOk()
            ->assertSee('Milik Saya')
            ->assertDontSee('Milik Agen Lain');

        $this->actingAs($agen)->get(route('admin.blog.edit', $mine))->assertOk();
        $this->actingAs($agen)->get(route('admin.blog.edit', $theirs))->assertForbidden();
        $this->actingAs($agen)->put(route('admin.blog.update', $theirs), $this->payload())->assertForbidden();
        $this->actingAs($agen)->delete(route('admin.blog.destroy', $theirs))->assertForbidden();
    }

    public function test_admin_manages_every_article(): void
    {
        $post = $this->article(['created_by' => $this->agen()->id, 'title' => 'Milik Agen Lain', 'slug' => 'milik-agen-lain']);

        $this->actingAs($this->admin())->get('/admin/blog')->assertOk()->assertSee('Milik Agen Lain');

        $this->actingAs($this->admin())
            ->put(route('admin.blog.update', $post), $this->payload(['title' => 'Diubah Admin']))
            ->assertSessionHasNoErrors();

        $this->assertSame('diubah-admin', $post->fresh()->slug);
    }

    public function test_a_cover_image_is_stored_and_replaced(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/blog', $this->payload([
                'cover' => UploadedFile::fake()->create('sampul.jpg', 200, 'image/jpeg'),
            ]))
            ->assertSessionHasNoErrors();

        $post = BlogPost::firstOrFail();
        Storage::disk('uploads')->assertExists($post->cover_image);
        $first = $post->cover_image;

        $this->actingAs($this->admin())
            ->put(route('admin.blog.update', $post), $this->payload([
                'cover' => UploadedFile::fake()->create('baru.png', 200, 'image/png'),
            ]))
            ->assertSessionHasNoErrors();

        $this->assertNotSame($first, $post->fresh()->cover_image);
        Storage::disk('uploads')->assertMissing($first);
    }

    public function test_removing_the_cover_deletes_the_file(): void
    {
        $post = $this->article(['cover_image' => 'images/blog/lama.jpg']);
        Storage::disk('uploads')->put('images/blog/lama.jpg', 'bytes');

        $this->actingAs($this->admin())
            ->put(route('admin.blog.update', $post), $this->payload(['remove_cover' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertNull($post->fresh()->cover_image);
        Storage::disk('uploads')->assertMissing('images/blog/lama.jpg');
    }

    public function test_deleting_an_article_removes_its_cover(): void
    {
        $post = $this->article(['cover_image' => 'images/blog/hapus.jpg']);
        Storage::disk('uploads')->put('images/blog/hapus.jpg', 'bytes');

        $this->actingAs($this->admin())
            ->delete(route('admin.blog.destroy', $post))
            ->assertRedirect(route('admin.blog.index'));

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
        Storage::disk('uploads')->assertMissing('images/blog/hapus.jpg');
    }

    public function test_a_title_and_a_body_are_required(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/blog', ['title' => '', 'content' => ''])
            ->assertSessionHasErrors(['title', 'content']);
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin/blog')->assertRedirect('/login');
    }

    public function test_the_editor_pages_load_quill_in_the_head(): void
    {
        $post = $this->article();

        $this->actingAs($this->admin())->get('/admin/blog/create')
            ->assertOk()
            ->assertSee('data-quill="content"', false)
            ->assertSeeInOrder(['vendor/quill/quill.snow.css', '<body']);

        $this->actingAs($this->admin())->get(route('admin.blog.edit', $post))
            ->assertOk()
            ->assertSee('data-quill="content"', false)
            ->assertSeeInOrder(['vendor/quill/quill.snow.css', '<body']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function test_the_title_header_searches_and_the_date_header_sorts(): void
    {
        $admin = $this->admin();

        $this->article(['title' => 'Panduan KPR', 'slug' => 'panduan-kpr', 'published_at' => now()->subDays(3)]);
        $this->article(['title' => 'Pajak Rumah', 'slug' => 'pajak-rumah', 'published_at' => now()->subDay()]);

        $this->actingAs($admin)->get('/admin/blog?judul=kpr')
            ->assertOk()
            ->assertSee('Panduan KPR')
            ->assertDontSee('Pajak Rumah');

        $this->actingAs($admin)->get('/admin/blog?urut=terbaru')
            ->assertOk()
            ->assertSeeInOrder(['Pajak Rumah', 'Panduan KPR']);

        $this->actingAs($admin)->get('/admin/blog?urut=terlama')
            ->assertOk()
            ->assertSeeInOrder(['Panduan KPR', 'Pajak Rumah']);
    }

    public function test_the_date_link_keeps_the_title_search(): void
    {
        $this->article(['title' => 'Panduan KPR', 'slug' => 'panduan-kpr']);

        $this->actingAs($this->admin())->get('/admin/blog?judul=kpr')
            ->assertOk()
            ->assertSee('judul=kpr', false);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Artikel Uji',
            'excerpt' => 'Ringkasan artikel.',
            'content' => '<p>Isi artikel uji.</p>',
            'is_published' => '1',
        ], $overrides);
    }

    private function article(array $attributes = []): BlogPost
    {
        return BlogPost::create(array_merge([
            'created_by' => $this->agen()->id,
            'title' => 'Artikel Tersimpan',
            'slug' => 'artikel-tersimpan',
            'excerpt' => 'Ringkasan.',
            'content' => '<p>Isi.</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ], $attributes));
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
