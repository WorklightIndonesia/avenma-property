<?php

namespace Tests\Feature\Admin;

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

    public function test_agen_sees_only_their_own_listings(): void
    {
        $agen = $this->agen();
        $this->listing(['title' => 'Milik Budi', 'created_by' => $agen->id]);
        $this->listing(['title' => 'Milik Agen Lain', 'created_by' => $this->agen()->id]);

        $this->actingAs($agen)
            ->get('/admin/listings')
            ->assertOk()
            ->assertSee('Milik Budi')
            ->assertDontSee('Milik Agen Lain');
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

    public function test_agen_creates_a_listing_under_their_own_name(): void
    {
        $agen = $this->agen(['phone' => '081234567890']);

        $this->actingAs($agen)
            ->post('/admin/listings', $this->payload())
            ->assertRedirect();

        $listing = Listing::firstOrFail();

        $this->assertSame($agen->id, $listing->created_by);
        $this->assertSame('081234567890', $listing->contact_phone);
        $this->assertSame('rumah-uji', $listing->slug);
        $this->assertSame(1_250_000_000, $listing->price);
        $this->assertTrue($listing->is_published);
    }

    public function test_a_given_contact_phone_wins_over_the_agen_number(): void
    {
        $agen = $this->agen(['phone' => '081234567890']);

        $this->actingAs($agen)
            ->post('/admin/listings', $this->payload(['contact_phone' => '0812-1111-2222']))
            ->assertRedirect();

        $this->assertSame('0812-1111-2222', Listing::firstOrFail()->contact_phone);
    }

    public function test_duplicate_titles_get_a_distinct_slug(): void
    {
        $agen = $this->agen();

        $this->actingAs($agen)->post('/admin/listings', $this->payload(['title' => 'Rumah Sama']))->assertRedirect();
        $this->actingAs($agen)->post('/admin/listings', $this->payload(['title' => 'Rumah Sama']))->assertRedirect();

        $this->assertSame(['rumah-sama', 'rumah-sama-2'], Listing::orderBy('id')->pluck('slug')->all());
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

    public function test_agen_cannot_open_another_agens_listing(): void
    {
        $listing = $this->listing(['created_by' => $this->agen()->id]);

        $this->actingAs($this->agen())
            ->get(route('admin.listings.edit', $listing))
            ->assertForbidden();
    }

    public function test_agen_cannot_update_another_agens_listing(): void
    {
        $listing = $this->listing(['created_by' => $this->agen()->id]);

        $this->actingAs($this->agen())
            ->put(route('admin.listings.update', $listing), $this->payload(['title' => 'Dibajak']))
            ->assertForbidden();

        $this->assertSame('Rumah Uji', $listing->refresh()->title);
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

    public function test_agen_cannot_delete_another_agens_listing(): void
    {
        $listing = $this->listing(['created_by' => $this->agen()->id]);

        $this->actingAs($this->agen())
            ->delete(route('admin.listings.destroy', $listing))
            ->assertForbidden();

        $this->assertNotSoftDeleted($listing);
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
            ->put(route('admin.listings.update', $listing), $this->payload(['latitude' => '', 'longitude' => '']))
            ->assertRedirect();

        $this->assertNull($listing->refresh()->latitude);
        $this->assertNull($listing->longitude);
    }

    public function test_half_a_point_is_rejected(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload(['latitude' => '-6.2615']))
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
        return User::factory()->create(array_merge(['role' => User::ROLE_AGEN], $attributes));
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
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
            'property_type' => 'rumah',
            'status' => 'dijual',
            'price' => '1.250.000.000',
            'is_published' => '1',
        ], $attributes);
    }
}
