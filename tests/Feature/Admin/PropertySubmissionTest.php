<?php

namespace Tests\Feature\Admin;

use App\Models\PropertySubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_submission_screens(): void
    {
        $this->get('/admin/titip-properti')->assertRedirect('/login');

        $this->get(route('admin.submissions.show', $this->submission()))->assertRedirect('/login');
    }

    public function test_the_admin_sees_the_list_and_the_detail(): void
    {
        $submission = $this->submission();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/titip-properti')
            ->assertOk()
            ->assertSee($submission->owner_name)
            ->assertSee('Jl. Melati No. 1, Jakarta');

        $this->actingAs($admin)
            ->get(route('admin.submissions.show', $submission))
            ->assertOk()
            ->assertSee('Data properti')
            ->assertSee('Hubungi via WhatsApp')
            ->assertSee('Buat listing dari data ini');
    }

    public function test_the_commission_amount_follows_the_stored_rate(): void
    {
        $submission = $this->submission(['price' => 1_000_000_000, 'commission_rate' => 5]);

        $this->assertSame(50_000_000, $submission->commissionAmount());
    }

    public function test_the_admin_updates_the_status_and_the_commission_rate(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->admin())
            ->patch(route('admin.submissions.update', $submission), [
                'status' => 'dihubungi',
                'commission_rate' => 3.5,
            ])
            ->assertRedirect();

        $submission->refresh();

        $this->assertSame('dihubungi', $submission->status);
        $this->assertSame(3.5, $submission->commission_rate);
    }

    public function test_an_impossible_commission_rate_is_rejected(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->admin())
            ->patch(route('admin.submissions.update', $submission), [
                'status' => 'baru',
                'commission_rate' => 120,
            ])
            ->assertSessionHasErrors('commission_rate');

        $this->assertSame(2.5, $submission->refresh()->commission_rate);
    }

    public function test_the_search_and_filters_narrow_the_list(): void
    {
        $this->submission(['owner_name' => 'Budi', 'address' => 'Jl. Melati']);
        $this->submission(['owner_name' => 'Sari', 'address' => 'Jl. Anggrek', 'offer' => 'sewa', 'status' => 'selesai']);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/titip-properti?q=melati')
            ->assertOk()
            ->assertSee('Budi')
            ->assertDontSee('Sari');

        $this->actingAs($admin)
            ->get('/admin/titip-properti?status=selesai')
            ->assertOk()
            ->assertSee('Sari')
            ->assertDontSee('Budi');

        $this->actingAs($admin)
            ->get('/admin/titip-properti?offer=sewa')
            ->assertOk()
            ->assertSee('Sari')
            ->assertDontSee('Budi');

        $this->actingAs($admin)
            ->get('/admin/titip-properti?q=budi&status=baru&offer=jual')
            ->assertOk()
            ->assertSee('Budi')
            ->assertDontSee('Sari');
    }

    public function test_the_listing_form_is_prefilled_from_a_submission(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->admin())
            ->get(route('admin.listings.create', ['titip' => $submission->id]))
            ->assertOk()
            ->assertSee('Data diambil dari pengajuan titip properti Budi')
            ->assertSee('name="submission_id"', false)
            ->assertSee('value="'.$submission->id.'"', false)
            // Draft title and address reach the form through the listing model.
            ->assertSee('Rumah Siap Huni di Jl. Melati No. 1, Jakarta')
            // Not published until the admin completes the region and photos.
            ->assertDontSee('value="1" checked', false);
    }

    public function test_a_converted_submission_is_not_offered_for_prefill(): void
    {
        $submission = $this->submission(['listing_id' => $this->listing()->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.listings.create', ['titip' => $submission->id]))
            ->assertOk()
            ->assertDontSee('name="submission_id"', false);
    }

    public function test_the_prefill_carries_an_other_type_and_legality_as_specs(): void
    {
        $submission = $this->submission([
            'property_type' => 'lainnya',
            'property_type_other' => 'Gudang berpendingin',
            'certificate' => 'lainnya',
            'certificate_other' => 'PPJB warisan',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.listings.create', ['titip' => $submission->id]))
            ->assertOk()
            ->assertSee('Jenis: Gudang berpendingin')
            ->assertSee('Legalitas: PPJB warisan');
    }

    public function test_saving_the_listing_marks_the_submission_as_done(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->admin())
            ->post('/admin/listings', [
                'title' => 'Rumah Titip Budi',
                'description' => 'Deskripsi properti.',
                'location_id' => $this->location()->id,
                'listing_type' => 'primary',
                'property_type' => 'rumah_siap_huni',
                'status' => 'dijual',
                'price' => '1.500.000.000',
                'submission_id' => $submission->id,
            ])
            ->assertRedirect();

        $submission->refresh();

        $this->assertNotNull($submission->listing_id);
        $this->assertSame('selesai', $submission->status);
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function submission(array $attributes = []): PropertySubmission
    {
        return PropertySubmission::create(array_merge([
            'owner_name' => 'Budi',
            'phone' => '081234567890',
            'property_type' => 'rumah_siap_huni',
            'address' => 'Jl. Melati No. 1, Jakarta',
            'offer' => 'jual',
            'land_area' => 120,
            'building_area' => 90,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'facing' => 'utara',
            'certificate' => 'shm',
            'price' => 1_500_000_000,
            'commission_rate' => 2.5,
            'status' => 'baru',
        ], $attributes));
    }

    private function listing(array $attributes = []): \App\Models\Listing
    {
        return \App\Models\Listing::create(array_merge([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => 'rumah-uji',
            'description' => 'Deskripsi.',
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ], $attributes));
    }
}
