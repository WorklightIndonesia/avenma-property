<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware and BlogPostPolicy decide access
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:600'],
            'content' => ['required', 'string'],

            // shortcut: no WebP conversion until Task 14
            'cover' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
            'remove_cover' => ['nullable', 'boolean'],
            'is_published' => ['boolean'],
        ];
    }

    /**
     * Unchecked checkboxes never reach the server, so normalize them here.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'remove_cover' => $this->boolean('remove_cover'),
        ]);
    }
}
