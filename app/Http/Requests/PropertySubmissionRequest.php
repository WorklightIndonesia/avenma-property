<?php

namespace App\Http\Requests;

use App\Models\ListingOption;
use App\Models\PropertySubmission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertySubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route is public; the throttle and honeypot guard it
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^08\d{7,12}$/'],
            'property_type' => ['required', Rule::in([
                ...array_keys(ListingOption::options('property_type')),
                PropertySubmission::OTHER,
            ])],
            'property_type_other' => [
                'nullable', 'string', 'max:100',
                Rule::requiredIf(fn () => $this->input('property_type') === PropertySubmission::OTHER),
            ],
            'address' => ['required', 'string', 'max:500'],
            'offer' => ['required', Rule::in(array_keys(PropertySubmission::OFFERS))],
            'land_area' => ['nullable', 'integer', 'between:0,1000000'],
            'building_area' => ['nullable', 'integer', 'between:0,1000000'],
            'bedrooms' => ['nullable', 'integer', 'between:0,99'],
            'bathrooms' => ['nullable', 'integer', 'between:0,99'],
            'facing' => ['nullable', Rule::in(array_keys(PropertySubmission::FACINGS))],
            'certificate' => ['nullable', Rule::in([
                ...array_keys(ListingOption::options('certificate')),
                PropertySubmission::OTHER,
            ])],
            'certificate_other' => [
                'nullable', 'string', 'max:100',
                Rule::requiredIf(fn () => $this->input('certificate') === PropertySubmission::OTHER),
            ],
            'price' => ['required', 'integer', 'min:1', 'max:100000000000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'owner_name.required' => 'Nama pemilik wajib diisi.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor WhatsApp harus diawali 08 dan berisi 9–14 angka.',
            'property_type.required' => 'Jenis properti wajib dipilih.',
            'property_type_other.required' => 'Tulis jenis properti lainnya.',
            'address.required' => 'Alamat properti wajib diisi.',
            'offer.required' => 'Pilih titip jual atau sewa.',
            'certificate_other.required' => 'Tulis legalitas lainnya.',
            'price.required' => 'Harga wajib diisi.',
        ];
    }

    /**
     * The price is typed as rupiah ("1.250.000.000") and the phone may arrive
     * as 62… or +62…; both are stored in one form.
     */
    protected function prepareForValidation(): void
    {
        $digits = preg_replace('/\D+/', '', (string) $this->input('phone'));

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        }

        $this->merge([
            'phone' => $digits,
            'price' => preg_replace('/\D+/', '', (string) $this->input('price')),
        ]);
    }
}
