<?php

namespace Tests\Feature\Admin;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListingFloorPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
    }

    public function test_rooms_are_stored_with_the_listing(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload([
                'rooms' => json_encode([
                    ['floor' => 1, 'label' => 'R. Tamu', 'x' => 0, 'y' => 2, 'w' => 4, 'h' => 3, 'tone' => 'biasa'],
                    ['floor' => 2, 'label' => 'K. Tidur 1', 'x' => 4, 'y' => 2, 'w' => 4, 'h' => 3, 'tone' => 'utama'],
                ]),
            ]))
            ->assertRedirect();

        $listing = Listing::firstOrFail();

        $this->assertCount(2, $listing->rooms);
        $this->assertDatabaseHas('listing_rooms', [
            'listing_id' => $listing->id,
            'floor' => 2,
            'label' => 'K. Tidur 1',
            'x' => 4,
            'w' => 4,
            'tone' => 'utama',
        ]);
    }

    public function test_saving_replaces_the_denah_instead_of_appending_to_it(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id]);

        $listing->rooms()->create(['floor' => 1, 'label' => 'Lama', 'x' => 0, 'y' => 0, 'w' => 2, 'h' => 2, 'tone' => 'biasa']);

        $this->actingAs($agen)
            ->put(route('admin.listings.update', $listing), $this->payload([
                'rooms' => json_encode([
                    ['floor' => 1, 'label' => 'Baru', 'x' => 0, 'y' => 0, 'w' => 3, 'h' => 2, 'tone' => 'luar'],
                ]),
            ]))
            ->assertRedirect();

        $this->assertSame(['Baru'], $listing->rooms()->pluck('label')->all());
    }

    public function test_saving_without_rooms_clears_the_denah(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id]);

        $listing->rooms()->create(['floor' => 1, 'label' => 'Lama', 'x' => 0, 'y' => 0, 'w' => 2, 'h' => 2, 'tone' => 'biasa']);

        $this->actingAs($agen)
            ->put(route('admin.listings.update', $listing), $this->payload(['rooms' => '']))
            ->assertRedirect();

        $this->assertSame(0, $listing->rooms()->count());
    }

    public function test_a_room_that_leaves_the_canvas_is_rejected(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload([
                'rooms' => json_encode([
                    ['floor' => 1, 'label' => 'Terlalu lebar', 'x' => 11, 'y' => 0, 'w' => 2, 'h' => 2, 'tone' => 'biasa'],
                ]),
            ]))
            ->assertSessionHasErrors('rooms');

        $this->assertDatabaseCount('listings', 0);
    }

    public function test_overlapping_rooms_on_one_floor_are_rejected(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload([
                'rooms' => json_encode([
                    ['floor' => 1, 'label' => 'R. Tamu', 'x' => 0, 'y' => 0, 'w' => 4, 'h' => 4, 'tone' => 'biasa'],
                    ['floor' => 1, 'label' => 'Dapur', 'x' => 3, 'y' => 3, 'w' => 4, 'h' => 4, 'tone' => 'biasa'],
                ]),
            ]))
            ->assertSessionHasErrors('rooms');

        $this->assertDatabaseCount('listings', 0);
    }

    public function test_the_same_rectangle_is_allowed_on_different_floors(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload([
                'rooms' => json_encode([
                    ['floor' => 1, 'label' => 'R. Tamu', 'x' => 0, 'y' => 0, 'w' => 4, 'h' => 4, 'tone' => 'biasa'],
                    ['floor' => 2, 'label' => 'K. Tidur 1', 'x' => 0, 'y' => 0, 'w' => 4, 'h' => 4, 'tone' => 'biasa'],
                ]),
            ]))
            ->assertRedirect();

        $this->assertDatabaseCount('listing_rooms', 2);
    }

    public function test_a_room_without_a_name_is_rejected(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/listings', $this->payload([
                'rooms' => json_encode([
                    ['floor' => 1, 'label' => '', 'x' => 0, 'y' => 0, 'w' => 2, 'h' => 2, 'tone' => 'biasa'],
                ]),
            ]))
            ->assertSessionHasErrors('rooms.0.label');
    }

    public function test_the_public_page_draws_the_rooms_and_the_land_caption(): void
    {
        $listing = $this->listing(['slug' => 'rumah-denah', 'land_area' => 60]);
        $listing->rooms()->createMany([
            ['floor' => 1, 'label' => 'R. Tamu', 'x' => 0, 'y' => 4, 'w' => 6, 'h' => 4, 'tone' => 'biasa'],
            ['floor' => 2, 'label' => 'K. Tidur 1', 'x' => 0, 'y' => 0, 'w' => 6, 'h' => 4, 'tone' => 'utama'],
        ]);

        $this->get('/properti/rumah-denah')
            ->assertOk()
            ->assertSee('Denah lantai 1')
            ->assertSee('Denah lantai 2')
            ->assertSee('R. Tamu')
            ->assertSee('Kavling 60 m² · skala ilustratif');
    }

    public function test_the_detail_page_has_no_denah_section_when_nothing_is_drawn(): void
    {
        $this->listing(['slug' => 'rumah-tanpa-denah']);

        $this->get('/properti/rumah-tanpa-denah')
            ->assertOk()
            ->assertDontSee('Denah lantai');
    }

    public function test_an_uploaded_image_wins_over_the_drawn_rooms(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id, 'slug' => 'rumah-denah-gambar']);
        $listing->rooms()->create(['floor' => 1, 'label' => 'R. Tamu', 'x' => 0, 'y' => 0, 'w' => 2, 'h' => 2, 'tone' => 'biasa']);

        $this->actingAs($agen)
            ->put(route('admin.listings.update', $listing), $this->payload([
                'floor_plan' => UploadedFile::fake()->create('denah.jpg', 80, 'image/jpeg'),
            ]))
            ->assertRedirect();

        $listing->refresh();

        $this->assertNotNull($listing->floor_plan_path);
        Storage::disk('uploads')->assertExists($listing->floor_plan_path);

        $this->get(route('public.listings.show', $listing->refresh()))
            ->assertOk()
            ->assertSee($listing->floor_plan_path, false)
            ->assertDontSee('Denah lantai');
    }

    public function test_removing_the_uploaded_image_deletes_the_file(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id]);

        Storage::disk('uploads')->put('images/floor-plans/1/denah.webp', 'fake bytes');
        $listing->floor_plan_path = 'images/floor-plans/1/denah.webp';
        $listing->save();

        $this->actingAs($agen)
            ->put(route('admin.listings.update', $listing), $this->payload(['remove_floor_plan' => '1']))
            ->assertRedirect();

        $this->assertNull($listing->refresh()->floor_plan_path);
        Storage::disk('uploads')->assertMissing('images/floor-plans/1/denah.webp');
    }

    public function test_the_edit_form_carries_the_drawn_rooms_into_the_editor(): void
    {
        $agen = $this->agen();
        $listing = $this->listing(['created_by' => $agen->id]);
        $listing->rooms()->create(['floor' => 2, 'label' => 'K. Tidur 1', 'x' => 3, 'y' => 4, 'w' => 5, 'h' => 2, 'tone' => 'utama']);

        $this->actingAs($agen)
            ->get(route('admin.listings.edit', $listing))
            ->assertOk()
            ->assertSee('name="rooms"', false)
            ->assertSee('floorPlan(', false)
            ->assertSee('K. Tidur 1');
    }

    private function agen(array $attributes = []): User
    {
        return User::factory()->create($attributes);
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
