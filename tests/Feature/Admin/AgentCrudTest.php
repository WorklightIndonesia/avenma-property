<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\Listing;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
    }

    public function test_guests_are_redirected_from_the_agent_screen(): void
    {
        $this->get('/admin/agents')->assertRedirect('/login');
    }

    public function test_agen_cannot_reach_the_agent_screens(): void
    {
        $agen = User::factory()->create(['role' => User::ROLE_AGEN]);
        $agent = $this->agent();

        $this->actingAs($agen)->get('/admin/agents')->assertForbidden();
        $this->actingAs($agen)->get('/admin/agents/create')->assertForbidden();
        $this->actingAs($agen)->get(route('admin.agents.edit', $agent))->assertForbidden();
    }

    public function test_admin_sees_the_agent_screens(): void
    {
        $agent = $this->agent();

        $this->actingAs($this->admin())->get('/admin/agents')->assertOk()->assertSee($agent->name);
        $this->actingAs($this->admin())->get('/admin/agents/create')->assertOk();
        $this->actingAs($this->admin())->get(route('admin.agents.edit', $agent))->assertOk()->assertSee($agent->email);
    }

    public function test_admin_creates_an_agent_with_a_photo(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/agents', $this->payload([
                'photo' => UploadedFile::fake()->create('foto.jpg', 120, 'image/jpeg'),
            ]))
            ->assertRedirect(route('admin.agents.index'));

        $agent = Agent::where('email', 'budi@example.com')->firstOrFail();

        $this->assertSame('Budi', $agent->name);
        $this->assertSame('Sales Representative', $agent->title);
        $this->assertSame('089630999996', $agent->phone);
        $this->assertStringStartsWith('images/agents/'.$agent->id.'/', $agent->photo_path);
        Storage::disk('uploads')->assertExists($agent->photo_path);
    }

    public function test_phone_is_required_and_must_start_with_08(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/agents', $this->payload(['phone' => '']))
            ->assertSessionHasErrors('phone');

        $this->actingAs($this->admin())
            ->post('/admin/agents', $this->payload(['phone' => '6281234567890']))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('agents', 0);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $this->agent(['email' => 'budi@example.com']);

        $this->actingAs($this->admin())
            ->post('/admin/agents', $this->payload())
            ->assertSessionHasErrors('email');
    }

    public function test_update_without_a_photo_keeps_the_old_one(): void
    {
        $agent = $this->agent(['photo_path' => 'images/agents/1/lama.webp']);

        $this->actingAs($this->admin())
            ->put(route('admin.agents.update', $agent), $this->payload(['name' => 'Nama Baru']))
            ->assertRedirect(route('admin.agents.index'));

        $agent->refresh();

        $this->assertSame('Nama Baru', $agent->name);
        $this->assertSame('images/agents/1/lama.webp', $agent->photo_path);
    }

    public function test_a_new_photo_replaces_the_old_file(): void
    {
        Storage::disk('uploads')->put('images/agents/1/lama.webp', 'x');

        $agent = $this->agent(['photo_path' => 'images/agents/1/lama.webp']);

        $this->actingAs($this->admin())
            ->put(route('admin.agents.update', $agent), $this->payload([
                'photo' => UploadedFile::fake()->create('baru.jpg', 120, 'image/jpeg'),
            ]))
            ->assertRedirect(route('admin.agents.index'));

        $agent->refresh();

        $this->assertNotSame('images/agents/1/lama.webp', $agent->photo_path);
        Storage::disk('uploads')->assertMissing('images/agents/1/lama.webp');
        Storage::disk('uploads')->assertExists($agent->photo_path);
    }

    public function test_deleting_an_agent_returns_its_listings_to_avenma(): void
    {
        $agent = $this->agent();
        $listing = $this->listing(['agent_id' => $agent->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.agents.destroy', $agent))
            ->assertRedirect(route('admin.agents.index'));

        $this->assertDatabaseMissing('agents', ['id' => $agent->id]);
        $this->assertNull($listing->refresh()->agent_id);
    }

    public function test_deleting_an_agent_removes_its_photo(): void
    {
        Storage::disk('uploads')->put('images/agents/1/foto.webp', 'x');

        $agent = $this->agent(['photo_path' => 'images/agents/1/foto.webp']);

        $this->actingAs($this->admin())->delete(route('admin.agents.destroy', $agent));

        Storage::disk('uploads')->assertMissing('images/agents/1/foto.webp');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function agent(array $attributes = []): Agent
    {
        return Agent::create(array_merge([
            'name' => 'Budi',
            'title' => 'Sales Representative',
            'phone' => '089630999996',
            'email' => 'budi@example.com',
        ], $attributes));
    }

    private function listing(array $attributes = []): Listing
    {
        return Listing::create(array_merge([
            'location_id' => Location::create([
                'name' => 'Pondok Aren',
                'slug' => 'pondok-aren',
                'province' => 'Banten',
                'city' => 'Kota Tangerang Selatan',
                'code' => '3674010',
            ])->id,
            'title' => 'Rumah Uji',
            'slug' => 'rumah-uji',
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
            'name' => 'Budi',
            'title' => 'Sales Representative',
            'phone' => '089630999996',
            'email' => 'budi@example.com',
        ], $attributes);
    }
}
