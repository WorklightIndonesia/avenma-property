<?php

namespace Tests\Feature\Admin;

use App\Models\Developer;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeveloperCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_developer_screen(): void
    {
        $this->get('/admin/developers')->assertRedirect('/login');
    }

    public function test_agen_sees_the_developer_screens(): void
    {
        $developer = Developer::create(['name' => 'Ciputra', 'slug' => 'ciputra']);
        $agen = $this->agen();

        $this->actingAs($agen)->get('/admin/developers')->assertOk()->assertSee('Ciputra');
        $this->actingAs($agen)->get('/admin/developers/create')->assertOk();
        $this->actingAs($agen)->get(route('admin.developers.edit', $developer))->assertOk()->assertSee('Ciputra');
    }

    public function test_agen_can_create_a_developer(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/developers', ['name' => 'Ciputra'])
            ->assertRedirect(route('admin.developers.index'));

        $developer = Developer::firstOrFail();

        $this->assertSame('Ciputra', $developer->name);
        $this->assertSame('ciputra', $developer->slug);
    }

    public function test_duplicate_names_get_a_distinct_slug(): void
    {
        $this->actingAs($this->agen())->post('/admin/developers', ['name' => 'Sama']);
        $this->actingAs($this->agen())->post('/admin/developers', ['name' => 'Sama']);

        $this->assertSame(['sama', 'sama-2'], Developer::orderBy('id')->pluck('slug')->all());
    }

    public function test_name_is_required(): void
    {
        $this->actingAs($this->agen())
            ->post('/admin/developers', ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('developers', 0);
    }

    public function test_agen_can_update_a_developer(): void
    {
        $developer = Developer::create(['name' => 'Lama', 'slug' => 'lama']);

        $this->actingAs($this->agen())
            ->put(route('admin.developers.update', $developer), [
                'name' => 'Baru',
                'description' => 'Deskripsi baru',
            ])
            ->assertRedirect(route('admin.developers.index'));

        $developer->refresh();

        $this->assertSame('Baru', $developer->name);
        $this->assertSame('baru', $developer->slug);
        $this->assertSame('Deskripsi baru', $developer->description);
    }

    public function test_developer_in_use_cannot_be_deleted(): void
    {
        $developer = Developer::create(['name' => 'Dipakai', 'slug' => 'dipakai']);
        $this->listing($developer);

        $this->actingAs($this->agen())
            ->delete(route('admin.developers.destroy', $developer))
            ->assertSessionHasErrors('developer');

        $this->assertDatabaseHas('developers', ['id' => $developer->id]);
    }

    public function test_free_developer_can_be_deleted(): void
    {
        $developer = Developer::create(['name' => 'Lepas', 'slug' => 'lepas']);

        $this->actingAs($this->agen())
            ->delete(route('admin.developers.destroy', $developer))
            ->assertRedirect(route('admin.developers.index'));

        $this->assertDatabaseMissing('developers', ['id' => $developer->id]);
    }

    private function agen(): User
    {
        return User::factory()->create(['role' => User::ROLE_AGEN]);
    }

    private function listing(Developer $developer): Listing
    {
        return Listing::create([
            'developer_id' => $developer->id,
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => Str::random(10),
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ]);
    }
}
