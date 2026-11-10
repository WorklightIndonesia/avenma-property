<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\WhatsAppTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Every WhatsApp message a visitor can send, in one screen: the fixed system
 * templates plus the message of each service row.
 */
class WhatsAppTemplateController extends Controller
{
    public function edit(): View
    {
        return view('admin.whatsapp-templates.edit', [
            'templates' => WhatsAppTemplate::query()
                ->whereIn('key', array_keys(WhatsAppTemplate::TEMPLATES))
                ->get()
                ->keyBy('key'),
            'services' => Service::query()->ordered()->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'templates' => ['required', 'array'],
            'templates.*' => ['required', 'string', 'max:1000'],
            'services' => ['array'],
            'services.*' => ['nullable', 'string', 'max:1000'],
        ], [
            'templates.*.required' => 'Teks template tidak boleh kosong.',
            'templates.*.max' => 'Teks template maksimal 1000 karakter.',
            'services.*.max' => 'Teks layanan maksimal 1000 karakter.',
        ]);

        foreach ($validated['templates'] as $key => $message) {
            if (array_key_exists($key, WhatsAppTemplate::TEMPLATES)) {
                WhatsAppTemplate::query()->updateOrCreate(['key' => $key], ['message' => $message]);
            }
        }

        foreach ($validated['services'] ?? [] as $id => $message) {
            Service::query()->whereKey($id)->update(['message' => $message ?: null]);
        }

        return redirect()
            ->route('admin.whatsapp-templates.edit')
            ->with('status', 'Template WhatsApp disimpan.');
    }
}
