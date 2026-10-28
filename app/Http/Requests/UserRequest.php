<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
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
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user?->id)],
            // An agen without a number would fall back to the office number
            // on every listing they create, so require it for that role only.
            'phone' => ['nullable', 'string', 'max:30', Rule::requiredIf(fn () => $this->input('role') === User::ROLE_AGEN)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_AGEN])],
            'password' => [Rule::requiredIf($user === null), 'nullable', 'confirmed', Password::defaults()],
        ];
    }
}
