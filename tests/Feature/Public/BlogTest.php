<?php

namespace Tests\Feature\Public;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_articles_are_listed_newest_first(): void
    {
        $this->article(['title' => 'Artikel Lama', 'slug' => 'artikel-lama', 'published_at' => now()->subDays(5)]);
        $this->article(['title' => 'Artikel Baru', 'slug' => 'artikel-baru', 'published_at' => now()->subDay()]);

        $this->get('/blog')
            ->assertOk()
            ->assertSeeInOrder(['Artikel Baru', 'Artikel Lama']);
    }

    public function test_drafts_and_scheduled_articles_are_not_listed(): void
    {
        $this->article(['title' => 'Draf Tersembunyi', 'slug' => 'draf-tersembunyi', 'is_published' => false]);
        $this->article(['title' => 'Terbit Minggu Depan', 'slug' => 'terbit-nanti', 'published_at' => now()->addWeek()]);

        $this->get('/blog')
            ->assertOk()
            ->assertDontSee('Draf Tersembunyi')
            ->assertDontSee('Terbit Minggu Depan');
    }

    public function test_an_article_is_shown_by_slug_with_its_html(): void
    {
        $this->article([
            'title' => 'Membeli Rumah Pertama',
            'slug' => 'membeli-rumah-pertama',
            'excerpt' => 'Ringkasan singkat.',
            'content' => '<p>Paragraf pembuka.</p><h2>Biaya tersembunyi</h2><ul><li>Pajak pembeli</li></ul>',
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog/membeli-rumah-pertama')
            ->assertOk()
            ->assertSee('Membeli Rumah Pertama')
            ->assertSee('Ringkasan singkat.')
            ->assertSee('<h2>Biaya tersembunyi</h2>', false)
            ->assertSee('<li>Pajak pembeli</li>', false);
    }

    public function test_draft_and_unknown_articles_are_not_found(): void
    {
        $this->article(['slug' => 'draf-tersembunyi', 'is_published' => false]);

        $this->get('/blog/draf-tersembunyi')->assertNotFound();
        $this->get('/blog/tidak-ada')->assertNotFound();
    }

    public function test_the_cover_image_is_used_on_the_card_and_the_article(): void
    {
        $this->article([
            'slug' => 'artikel-bersampul',
            'title' => 'Artikel Bersampul',
            'cover_image' => 'images/blog/sampul.jpg',
        ]);

        $this->get('/blog')->assertOk()->assertSee('/images/blog/sampul.jpg', false);
        $this->get('/blog/artikel-bersampul')->assertOk()->assertSee('/images/blog/sampul.jpg', false);
    }

    public function test_the_paginator_matches_the_listing_one(): void
    {
        foreach (range(1, 10) as $number) {
            $this->article([
                'title' => 'Artikel '.$number,
                'slug' => 'artikel-'.$number,
                'published_at' => now()->subDays($number),
            ]);
        }

        $this->get('/blog')
            ->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSee('Berikutnya')
            ->assertSee('href="'.route('public.blog.index', ['page' => 2]).'"', false);
    }

    private function article(array $attributes = []): BlogPost
    {
        return BlogPost::create(array_merge([
            'created_by' => User::factory()->create(['role' => User::ROLE_AGEN, 'phone' => '089630999996'])->id,
            'title' => 'Artikel Uji',
            'slug' => 'artikel-uji',
            'excerpt' => 'Ringkasan.',
            'content' => '<p>Isi artikel.</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ], $attributes));
    }
}
