<?php

namespace Tests\Feature\Public;

use App\Models\PropertySubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_renders_the_form_and_its_cms_copy(): void
    {
        $this->get('/titip-properti')
            ->assertOk()
            ->assertSee('Titipkan properti Anda kepada Avenma')
            ->assertSee('Formulir titip properti')
            ->assertSee('name="owner_name"', false)
            ->assertSee('name="offer"', false)
            ->assertSee('name="price"', false)
            // "Lainnya" is offered for both dropdowns at the end of the options.
            ->assertSee('Lainnya');
    }

    public function test_a_sale_submission_is_stored_with_the_sale_commission(): void
    {
        $this->post('/titip-properti', $this->payload())
            ->assertRedirect(route('public.submissions.create'))
            ->assertSessionHas('status');

        $submission = PropertySubmission::firstOrFail();

        $this->assertSame('Budi', $submission->owner_name);
        $this->assertSame('081234567890', $submission->phone);
        $this->assertSame(1500000000, $submission->price);
        $this->assertSame(2.5, $submission->commission_rate);
        $this->assertSame('baru', $submission->status);
        $this->assertNull($submission->listing_id);
    }

    public function test_a_rental_submission_uses_the_rental_commission(): void
    {
        $this->post('/titip-properti', $this->payload(['offer' => 'sewa']));

        $this->assertSame(5.0, PropertySubmission::firstOrFail()->commission_rate);
    }

    public function test_the_phone_is_normalised_from_an_international_prefix(): void
    {
        $this->post('/titip-properti', $this->payload(['phone' => '+62 812-3456-7890']));

        $this->assertSame('081234567890', PropertySubmission::firstOrFail()->phone);
    }

    public function test_required_fields_are_rejected(): void
    {
        $this->post('/titip-properti', [])
            ->assertSessionHasErrors(['owner_name', 'phone', 'property_type', 'address', 'offer', 'price']);

        $this->assertDatabaseCount('property_submissions', 0);
    }

    public function test_an_unknown_property_type_or_certificate_is_rejected(): void
    {
        $this->post('/titip-properti', $this->payload(['property_type' => 'istana']))
            ->assertSessionHasErrors('property_type');

        $this->post('/titip-properti', $this->payload(['certificate' => 'surat karangan']))
            ->assertSessionHasErrors('certificate');

        $this->assertDatabaseCount('property_submissions', 0);
    }

    public function test_a_filled_honeypot_saves_nothing(): void
    {
        $this->post('/titip-properti', $this->payload(['website' => 'https://spam.example']))
            ->assertRedirect(route('public.submissions.create'));

        $this->assertDatabaseCount('property_submissions', 0);
    }

    public function test_an_other_property_type_is_stored_with_its_text(): void
    {
        $this->post('/titip-properti', $this->payload([
            'property_type' => 'lainnya',
            'property_type_other' => 'Gudang berpendingin',
        ]))->assertSessionHasNoErrors();

        $submission = PropertySubmission::firstOrFail();

        $this->assertSame('lainnya', $submission->property_type);
        $this->assertSame('Gudang berpendingin', $submission->property_type_other);
        $this->assertSame('Gudang berpendingin', $submission->typeLabel());
    }

    public function test_an_other_legality_is_stored_with_its_text(): void
    {
        $this->post('/titip-properti', $this->payload([
            'certificate' => 'lainnya',
            'certificate_other' => 'PPJB warisan',
        ]))->assertSessionHasNoErrors();

        $submission = PropertySubmission::firstOrFail();

        $this->assertSame('PPJB warisan', $submission->certificate_other);
        $this->assertSame('PPJB warisan', $submission->certificateLabel());
    }

    public function test_choosing_other_requires_its_text(): void
    {
        $this->post('/titip-properti', $this->payload(['property_type' => 'lainnya']))
            ->assertSessionHasErrors('property_type_other');

        $this->post('/titip-properti', $this->payload(['certificate' => 'lainnya']))
            ->assertSessionHasErrors('certificate_other');

        $this->assertDatabaseCount('property_submissions', 0);
    }

    public function test_typed_other_text_is_dropped_when_a_listed_option_is_chosen(): void
    {
        $this->post('/titip-properti', $this->payload([
            'property_type' => 'rumah_siap_huni',
            'property_type_other' => 'Sisa ketikan',
            'certificate' => 'shm',
            'certificate_other' => 'Sisa ketikan',
        ]));

        $submission = PropertySubmission::firstOrFail();

        $this->assertNull($submission->property_type_other);
        $this->assertNull($submission->certificate_other);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
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
            'price' => '1.500.000.000',
        ], $overrides);
    }
}
