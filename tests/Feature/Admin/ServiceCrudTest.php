<?php

namespace Tests\Feature\Admin;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_service_screen(): void
    {
        $this->get('/admin/layanan')->assertRedirect('/login');
    }

    public function test_admin_sees_the_service_screens(): void
    {
        $service = $this->service();

        $this->actingAs($this->admin())->get('/admin/layanan')->assertOk()->assertSee($service->title);
        $this->actingAs($this->admin())->get('/admin/layanan/create')->assertOk();
        $this->actingAs($this->admin())->get(route('admin.services.edit', $service))->assertOk()->assertSee($service->description);
    }

    public function test_admin_adds_a_service_at_the_end_of_the_list(): void
    {
        $this->service(['title' => 'Lama', 'sort_order' => 0]);

        $this->actingAs($this->admin())
            ->post('/admin/layanan', $this->payload(['title' => 'Baru']))
            ->assertRedirect(route('admin.services.index'));

        $service = Service::where('title', 'Baru')->firstOrFail();

        $this->assertSame(1, $service->sort_order);
        $this->assertSame(['Rumah', 'Ruko'], $service->tagsList());
    }

    public function test_title_and_description_are_required(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/layanan', ['title' => '', 'description' => ''])
            ->assertSessionHasErrors(['title', 'description']);

        $this->assertDatabaseCount('services', 0);
    }

    public function test_admin_updates_a_service(): void
    {
        $service = $this->service();

        $this->actingAs($this->admin())
            ->put(route('admin.services.update', $service), $this->payload(['title' => 'Nama Baru']))
            ->assertRedirect(route('admin.services.index'));

        $this->assertSame('Nama Baru', $service->refresh()->title);
    }

    public function test_admin_deletes_a_service(): void
    {
        $service = $this->service();

        $this->actingAs($this->admin())
            ->delete(route('admin.services.destroy', $service))
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_moving_a_service_swaps_it_with_its_neighbour(): void
    {
        $first = $this->service(['title' => 'Satu', 'sort_order' => 0]);
        $second = $this->service(['title' => 'Dua', 'sort_order' => 1]);

        $this->actingAs($this->admin())
            ->post(route('admin.services.move', [$second, 'up']))
            ->assertRedirect(route('admin.services.index'));

        $this->assertSame(1, $first->refresh()->sort_order);
        $this->assertSame(0, $second->refresh()->sort_order);
    }

    public function test_the_first_row_cannot_move_up(): void
    {
        $first = $this->service(['title' => 'Satu', 'sort_order' => 0]);
        $this->service(['title' => 'Dua', 'sort_order' => 1]);

        $this->actingAs($this->admin())->post(route('admin.services.move', [$first, 'up']));

        $this->assertSame(0, $first->refresh()->sort_order);
    }

    public function test_an_edit_reaches_the_public_page(): void
    {
        $service = $this->service(['title' => 'Nama Lama']);

        $this->actingAs($this->admin())
            ->put(route('admin.services.update', $service), $this->payload(['title' => 'Nama Baru']));

        $this->get('/layanan')->assertOk()->assertSee('Nama Baru')->assertDontSee('Nama Lama');
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function service(array $attributes = []): Service
    {
        return Service::create(array_merge([
            'title' => 'Konsultasi Properti',
            'tags' => 'Harga Pasar, Lokasi',
            'description' => 'Memberikan pertimbangan harga pasar dan lokasi.',
            'cta_label' => 'Konsultasi',
            'message' => 'Halo Avenma, saya ingin konsultasi properti.',
            'sort_order' => 0,
        ], $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $attributes = []): array
    {
        return array_merge([
            'title' => 'Konsultasi Properti',
            'tags' => 'Rumah, Ruko',
            'description' => 'Memberikan pertimbangan harga pasar dan lokasi.',
            'cta_label' => 'Konsultasi',
            'message' => 'Halo Avenma, saya ingin konsultasi properti.',
        ], $attributes);
    }
}
