<?php

namespace App\Http\Requests;

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
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'developer_id' => ['nullable', 'integer', 'exists:developers,id'],
            'listing_type' => ['required', Rule::in(['primary', 'secondary'])],
            'property_type' => ['required', Rule::in(['rumah', 'apartemen', 'townhouse', 'ruko', 'tanah'])],
            'status' => ['required', Rule::in(['dijual', 'disewa', 'baru'])],
            'price' => ['required', 'integer', 'min:1', 'max:100000000000'],
            'bedrooms' => ['nullable', 'integer', 'between:0,99'],
            'bathrooms' => ['nullable', 'integer', 'between:0,99'],
            'carport' => ['nullable', 'integer', 'between:0,99'],
            'land_area' => ['nullable', 'integer', 'between:0,1000000'],
            'building_area' => ['nullable', 'integer', 'between:0,1000000'],
            'address' => ['nullable', 'string', 'max:255'],
            'map_embed' => ['nullable', 'string', 'max:2000'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],

            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],

            'images' => ['nullable', 'array'],
            'images.*.label' => ['nullable', 'string', 'max:60'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer', 'between:0,999'],
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
        ]);
    }
}
