<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\HomeGalleryPhoto;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListingCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_listing_screen(): void
    {
        $this->get('/admin/listings')->assertRedirect('/login');
    }

    public function test_admin_sees_every_listing(): void
    {
        $this->listing(['title' => 'Milik Budi', 'created_by' => $this->agen()->id]);
        $this->listing(['title' => 'Milik Agen Lain', 'created_by' => $this->agen()->id]);

        $this->actingAs($this->admin())
            ->get('/admin/listings')
            ->assertOk()
            ->assertSee('Milik Budi')
            ->assertSee('Milik Agen Lain');
    }

    public function test_an_admin_can_flag_a_listing_as_featured(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/listings', $this->payload(['is_featured' => '1']))
            ->assertRedirect();

        $this->assertTrue(Listing::firstOrFail()->is_featured);
    }

    public function test_the_listing_form_offers_the_featured_checkbox(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/listings/create')
            ->assertOk()
            ->assertSee('Tandai sebagai unggulan');
    }

    public function test_the_form_offers_every_property_type(): void
    {
        $response = $this->actingAs($this->admin())
            ->get('/admin/listings/create')
            ->assertOk();

        foreach (Listing::PROPERTY_TYPES as $label) {
            $response->assertSee($label);
        }

        $response->assertSee('Tampilkan simulasi KPR untuk listing ini?');
    }

    public function test_certificate_is_optional_and_saved(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/listings', $this->payload(['certificate' => 'Surat Hak Milik']))
            ->assertRedirect();

        $this->assertSame('Surat Hak Milik', Listing::firstOrFail()->certificate);
    }

    public function test_the_kpr_switch_is_saved(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/listings', $this->payload(['show_kpr' => '1']))
            ->assertRedirect();

        $this->assertTrue(Listing::firstOrFail()->show_kpr);

        $this->actingAs($this->admin())
            ->post('/admin/listings', $this->payload(['title' => 'Rumah Kedua']))
            ->assertRedirect();

        $this->assertFalse(Listing::where('title', 'Rumah Kedua')->firstOrFail()->show_kpr);
    }

    public function test_the_index_warns_when_more_than_six_are_featured(): void
    {
        foreach (range(1, 7) as $index) {
            $this->listing(['title' => 'Unggulan '.$index, 'is_featured' => true]);
        }

        $this->actingAs($this->admin())
            ->get('/admin/listings')
            ->assertOk()
            ->assertSee('7 properti ditandai unggulan')
            ->assertSee('beranda hanya menampilkan 6');
    }

    public function test_agen_creates_a_listing_under_their_own_name(): void
    {
        $agen = $this->agen(['phone' => '081234567890']);

        $this->actingAs($agen)
            ->post('/admin/listings', $this->payload())
            ->assertRedirect();

        $listing = Listing::firstOrFail();

        $this->assertSame($agen->id, $listing->created_by);
        $this->assertNull($listing->agent_id);
        $this->assertSame('rumah-uji', $listing->slug);
        $this->assertSame(1_250_000_000, $listing->price);
        $this->assertTrue($listing->is_published);
    }

    public function test_the_form_carries_the_unsaved_changes_dialog(): void
    {
        $this->actingAs($this->agen())->get('/admin/listings/create')
            ->assertOk()
            ->assertSee('data-dirty-guard', false)
            ->assertSee('Tinggalkan halaman ini?')
            ->assertSee('Buang perubahan')
            ->assertSee('data-autofocus', false);
    }

    public function test_creating_a_listing_lands_on_the_photo_section(): void
    {
        $response = $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload());

        $response->assertRedirect();

        // Labels are still a second pass, so the redirect puts the admin on it
        // instead of at the top of a 2,000px form.
        $this->assertStringEndsWith('#foto', $response->headers->get('Location'));
    }

    public function test_the_chosen_agent_is_saved_on_the_listing(): void
    {
        $agent = Agent::create([
            'name' => 'Budi',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/listings', $this->payload(['agent_id' => $agent->id]))
            ->assertRedirect();

        $this->assertSame($agent->id, Listing::firstOrFail()->agent_id);
    }

    public function test_an_unknown_agent_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/listings', $this->payload(['agent_id' => 999]))
            ->assertSessionHasErrors('agent_id');
    }

    public function test_duplicate_titles_get_a_distinct_slug(): void
    {
        $agen = $this->agen();

        $this->actingAs($agen)->post('/admin/listings', $this->payload(['title' => 'Rumah Sama']))->assertRedirect();
        $this->actingAs($agen)->post('/admin/listings', $this->payload(['title' => 'Rumah Sama']))->assertRedirect();

        $this->assertSame(['rumah-sama', 'rumah-sama-2'], Listing::orderBy('id')->pluck('slug')->all());
    }

    public function test_a_slug_claimed_at_the_last_moment_is_retried(): void
    {
        $stolen = false;

        // Stand in for a second request that passed the same check a moment
        // before this one inserted its row.
        Listing::creating(function (Listing $listing) use (&$stolen) {
            if ($stolen) {
                return;
            }

            $stolen = true;

            Listing::withoutEvents(fn () => Listing::forceCreate([
                'location_id' => $this->location()->id,
                'title' => 'Rumah Uji',
                'slug' => $listing->slug,
                'description' => 'Disimpan request lain.',
                'listing_type' => 'primary',
                'property_type' => 'rumah_siap_huni',
                'status' => 'dijual',
                'price' => 900_000_000,
            ]));
        });

        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $mine = Listing::where('description', 'Deskripsi uji.')->firstOrFail();

        $this->assertSame('rumah-uji-2', $mine->slug);
        $this->assertSame(2, Listing::count());
    }

    public function test_agen_can_update_their_own_listing(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['slug' => 'rumah-lama', 'created_by' => $agen->id]);

        $this->actingAs($agen)
            ->put(route('admin.listings.update', $listing), $this->payload(['title' => 'Rumah Baru']))
            ->assertRedirect();

        $listing->refresh();

        $this->assertSame('Rumah Baru', $listing->title);
        $this->assertSame('rumah-baru', $listing->slug);
    }

    public function test_admin_can_open_any_listing(): void
    {
        $listing = $this->listing(['created_by' => $this->agen()->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.listings.edit', $listing))
            ->assertOk()
            ->assertSee('Rumah Uji');
    }

    public function test_agen_can_delete_their_own_listing(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id]);

        $this->actingAs($agen)
            ->delete(route('admin.listings.destroy', $listing))
            ->assertRedirect(route('admin.listings.index'));

        $this->assertSoftDeleted($listing);
    }

    public function test_the_delete_dialog_warns_when_a_photo_is_in_the_hero(): void
    {
        $listing = $this->listing();
        $photo = $listing->images()->create(['path' => 'images/listings/hero.webp', 'sort_order' => 1]);
        HomeGalleryPhoto::create(['listing_image_id' => $photo->id, 'sort_order' => 0]);

        $this->actingAs($this->admin())
            ->get('/admin/listings')
            ->assertOk()
            ->assertSee('Salah satu foto listing ini dipakai di galeri beranda');
    }

    public function test_the_dialog_stays_plain_without_a_hero_photo(): void
    {
        $this->listing();
        $this->listing(['title' => 'Rumah Lain']);

        $this->actingAs($this->admin())
            ->get('/admin/listings')
            ->assertOk()
            ->assertDontSee('dipakai di galeri beranda');
    }

    public function test_an_admin_deletes_without_the_agen_message(): void
    {
        $listing = $this->listing();
        $photo = $listing->images()->create(['path' => 'images/listings/hero.webp', 'sort_order' => 1]);
        HomeGalleryPhoto::create(['listing_image_id' => $photo->id, 'sort_order' => 0]);

        $this->actingAs($this->admin())
            ->delete(route('admin.listings.destroy', $listing))
            ->assertRedirect(route('admin.listings.index'))
            ->assertSessionMissing('warning');
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', [])
            ->assertSessionHasErrors(['title', 'description', 'location_id', 'price']);

        $this->assertDatabaseCount('listings', 0);
    }

    public function test_coordinates_are_saved_with_the_listing(): void
    {
        $agen = $this->agen();

        $this->actingAs($agen)
            ->post('/admin/listings', $this->payload([
                'latitude' => '-6.2615',
                'longitude' => '106.8',
            ]))
            ->assertRedirect();

        $listing = Listing::firstOrFail();

        $this->assertSame(-6.2615, $listing->latitude);
        $this->assertSame(106.8, $listing->longitude);

        $this->actingAs($agen)
            ->put(route('admin.listings.update', $listing), $this->payload(['latitude' => '', 'longitude' => '', 'is_published' => '0']))
            ->assertRedirect();

        $this->assertNull($listing->refresh()->latitude);
        $this->assertNull($listing->longitude);
    }

    public function test_a_published_listing_needs_a_map_point(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload(['latitude' => '', 'longitude' => '', 'is_published' => '1']))
            ->assertSessionHasErrors([
                'latitude' => 'Tandai titik lokasi di peta sebelum listing diterbitkan.',
            ]);

        $this->assertDatabaseCount('listings', 0);

        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload(['latitude' => '', 'longitude' => '', 'is_published' => '0']))
            ->assertRedirect();

        $this->assertDatabaseCount('listings', 1);
    }

    public function test_an_already_published_listing_is_editable_without_a_point(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id, 'is_published' => true]);

        $this->actingAs($agen)
            ->put(route('admin.listings.update', $listing), $this->payload([
                'title' => 'Rumah Uji Diperbarui',
                'latitude' => '',
                'longitude' => '',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('Rumah Uji Diperbarui', $listing->refresh()->title);
        $this->assertNull($listing->latitude);

        // Unpublishing and publishing again counts as a new publish.
        $draft = $this->listing(['created_by' => $agen->id, 'is_published' => false]);

        $this->actingAs($agen)
            ->put(route('admin.listings.update', $draft), $this->payload(['latitude' => '', 'longitude' => '']))
            ->assertSessionHasErrors('latitude');
    }

    public function test_half_a_point_is_rejected(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload(['latitude' => '-6.2615', 'longitude' => '']))
            ->assertSessionHasErrors('longitude');

        $this->assertDatabaseCount('listings', 0);
    }

    public function test_floors_are_saved_and_must_be_positive(): void
    {
        $agen = $this->agen();

        $this->actingAs($agen)
            ->post('/admin/listings', $this->payload(['floors' => 2]))
            ->assertRedirect();

        $this->assertSame(2, Listing::firstOrFail()->floors);

        $this->actingAs($agen)
            ->post('/admin/listings', $this->payload(['title' => 'Rumah Tanpa Lantai', 'floors' => 0]))
            ->assertSessionHasErrors('floors');
    }

    private function agen(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    private function admin(): User
    {
        return User::factory()->create();
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
