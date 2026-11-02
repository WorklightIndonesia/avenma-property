<?php

namespace App\Http\Requests;

use App\Models\Listing;
use App\Models\ListingOption;
use App\Models\ListingRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ListingRequest extends FormRequest
{
    private ?int $uploadSlots = null;

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
            // A listing being published needs its own point; the map card
            // otherwise shows Google's address search instead of the property.
            'latitude' => ['nullable', 'required_with:longitude', Rule::requiredIf(fn () => $this->publishesWithoutPoint()), 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'show_kpr' => ['boolean'],

            // Ten per upload, and never more than the total the listing may hold.
            'photos' => ['nullable', 'array', 'max:'.$this->maxUploads()],
            'photos.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],

            'images' => ['nullable', 'array'],
            'images.*.label' => ['nullable', 'string', 'max:60'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer', 'between:0,999'],

            // The editor posts one JSON string; prepareForValidation decodes it.
            'rooms' => ['nullable', 'array', 'max:'.ListingRoom::MAX],
            'rooms.*.floor' => ['required', 'integer', 'between:1,99'],
            'rooms.*.label' => ['required', 'string', 'max:40'],
            'rooms.*.x' => ['required', 'integer', 'between:0,'.(ListingRoom::COLS - 1)],
            'rooms.*.y' => ['required', 'integer', 'between:0,'.(ListingRoom::ROWS - 1)],
            'rooms.*.w' => ['required', 'integer', 'between:1,'.ListingRoom::COLS],
            'rooms.*.h' => ['required', 'integer', 'between:1,'.ListingRoom::ROWS],
            'rooms.*.tone' => ['required', Rule::in(array_keys(ListingRoom::TONES))],

            'floor_plan' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
            'remove_floor_plan' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'Tandai titik lokasi di peta sebelum listing diterbitkan.',
            'photos.max' => 'Maksimal '.$this->maxUploads().' foto bisa ditambahkan sekarang; batas tiap listing '.Listing::MAX_PHOTOS.' foto.',
            'rooms.max' => 'Maksimal '.ListingRoom::MAX.' ruangan per listing.',
            'rooms.*.label.required' => 'Setiap ruangan perlu nama.',
            'rooms.*.label.max' => 'Nama ruangan maksimal 40 karakter.',
        ];
    }

    /**
     * Only a listing that is being published now needs a point. Rows published
     * before that rule have none, so requiring one again would block every edit
     * to them; the public page already falls back to the address map.
     */
    private function publishesWithoutPoint(): bool
    {
        $listing = $this->route('listing');

        return $this->boolean('is_published')
            && (! $listing instanceof Listing || ! $listing->is_published);
    }

    /**
     * The editor draws rectangles on a cell grid; the request only checks the
     * shape, so a room that leaves the box or covers another one is refused.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $rooms = $this->input('rooms');

            if (! is_array($rooms) || $rooms === []) {
                return;
            }

            foreach ($rooms as $room) {
                if (! is_array($room) || ! isset($room['x'], $room['y'], $room['w'], $room['h'], $room['floor'])) {
                    return;
                }

                if ((int) $room['x'] + (int) $room['w'] > ListingRoom::COLS
                    || (int) $room['y'] + (int) $room['h'] > ListingRoom::ROWS) {
                    $validator->errors()->add('rooms', 'Ada ruangan yang melewati batas kotak denah.');

                    return;
                }
            }

            foreach ($rooms as $outer => $first) {
                foreach ($rooms as $inner => $second) {
                    if ($inner <= $outer || (int) $first['floor'] !== (int) $second['floor']) {
                        continue;
                    }

                    if ($this->overlaps($first, $second)) {
                        $validator->errors()->add('rooms', 'Ada ruangan yang bertumpuk di lantai '.$first['floor'].'. Geser atau kecilkan salah satunya.');

                        return;
                    }
                }
            }
        });
    }

    /**
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    private function overlaps(array $a, array $b): bool
    {
        return (int) $a['x'] < (int) $b['x'] + (int) $b['w']
            && (int) $b['x'] < (int) $a['x'] + (int) $a['w']
            && (int) $a['y'] < (int) $b['y'] + (int) $b['h']
            && (int) $b['y'] < (int) $a['y'] + (int) $a['h'];
    }

    /**
     * Ten per upload, and never more than the total the listing may still hold.
     * Saving adds photos to whatever is stored, so the count is the stored rows.
     */
    private function maxUploads(): int
    {
        if ($this->uploadSlots === null) {
            $listing = $this->route('listing');
            $remaining = Listing::MAX_PHOTOS - ($listing instanceof Listing ? $listing->images()->count() : 0);

            $this->uploadSlots = max(0, min(10, $remaining));
        }

        return $this->uploadSlots;
    }

    /**
     * The price field is typed as rupiah ("1.250.000.000"); store the digits.
     * Unchecked checkboxes never reach the server, so normalize them here.
     */
    protected function prepareForValidation(): void
    {
        $rooms = $this->input('rooms');

        $this->merge([
            'price' => preg_replace('/\D+/', '', (string) $this->input('price')),
            'rooms' => is_string($rooms) ? json_decode($rooms, true) : $rooms,
            'is_featured' => $this->boolean('is_featured'),
            'is_published' => $this->boolean('is_published'),
            'show_kpr' => $this->boolean('show_kpr'),
            'remove_floor_plan' => $this->boolean('remove_floor_plan'),
        ]);
    }
}
