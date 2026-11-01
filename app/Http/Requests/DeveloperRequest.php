<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeveloperRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware decides access
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'whatsapp' => ['required', 'string', 'regex:/^08\d{7,12}$/'],
            'email' => ['required', 'string', 'email', 'max:190'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama developer wajib diisi.',
            'whatsapp.required' => 'Nomor WhatsApp developer wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 dan berisi 9–14 angka.',
            'email.required' => 'Email developer wajib diisi.',
            'email.email' => 'Email developer tidak valid.',
        ];
    }
}
