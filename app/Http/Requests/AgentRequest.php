<?php

namespace App\Http\Requests;

use App\Models\Agent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware limits this to admin
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $agent = $this->route('agent');

        return [
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:100'],
            // The number is the WhatsApp link on every listing this agent holds.
            'phone' => ['required', 'string', 'regex:/^08\d{7,12}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:190', Rule::unique(Agent::class)->ignore($agent?->id)],
            'photo' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama agen wajib diisi.',
            'phone.required' => 'Nomor WhatsApp agen wajib diisi.',
            'phone.regex' => 'Nomor WhatsApp harus diawali 08 dan berisi 9–14 angka.',
            'email.required' => 'Email agen wajib diisi.',
            'email.email' => 'Email agen tidak valid.',
            'email.unique' => 'Email ini sudah dipakai agen lain.',
        ];
    }
}
