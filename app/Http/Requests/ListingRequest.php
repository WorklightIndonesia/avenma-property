<?php

namespace App\Http\Requests;

use App\Models\Listing;
use App\Models\ListingOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware and ListingPolicy decide access
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Saving adds photos to whatever the listing already has, so the cap
        // counts the stored rows, not just this upload.
        $listing = $this->route('listing');
        $remaining = Listing::MAX_PHOTOS - ($listing instanceof Listing ? $listing->images()->count() : 0);

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'developer_id' => ['nullable', 'integer', 'exists:developers,id'],
            'listing_type' => ['required', Rule::in(array_keys(ListingOption::options('listing_type')))],
            'property_type' => ['required', Rule::in(array_keys(ListingOption::options('property_type')))],
            'certificate' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(array_keys(ListingOption::options('status')))],
            'price' => ['required', 'integer', 'min:1', 'max:100000000000'],
            'bedrooms' => ['nullable', 'integer', 'between:0,99'],
            'bathrooms' => ['nullable', 'integer', 'between:0,99'],
            'carport' => ['nullable', 'integer', 'between:0,99'],
            'land_area' => ['nullable', 'integer', 'between:0,1000000'],
            'building_area' => ['nullable', 'integer', 'between:0,1000000'],
            'floors' => ['nullable', 'integer', 'between:1,99'],
            'address' => ['nullable', 'string', 'max:255'],
            // A point is only useful with both coordinates; either one alone is
            // a half-filled pin.
            // A published listing needs its own point; the map card otherwise
            // shows Google's address search instead of the property.
            'latitude' => ['nullable', 'required_with:longitude', Rule::requiredIf(fn () => $this->boolean('is_published')), 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'show_kpr' => ['boolean'],

            // Ten per upload, and never more than the total the listing may hold.
            'photos' => ['nullable', 'array', 'max:'.max(0, min(10, $remaining))],
            'photos.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],

            'images' => ['nullable', 'array'],
            'images.*.label' => ['nullable', 'string', 'max:60'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer', 'between:0,999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'Tandai titik lokasi di peta sebelum listing diterbitkan.',
            'photos.max' => 'Maksimal '.Listing::MAX_PHOTOS.' foto per listing. Hapus beberapa foto dulu sebelum menambah.',
        ];
    }

    /**
     * The price field is typed as rupiah ("1.250.000.000"); store the digits.
     * Unchecked checkboxes never reach the server, so normalize them here.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'price' => preg_replace('/\D+/', '', (string) $this->input('price')),
            'is_featured' => $this->boolean('is_featured'),
            'is_published' => $this->boolean('is_published'),
            'show_kpr' => $this->boolean('show_kpr'),
        ]);
    }
}
