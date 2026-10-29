<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', ['setting' => SiteSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Stored as 08…; accept 62… or +62… and strip spaces and dashes.
        $digits = preg_replace('/\D+/', '', (string) $request->input('whatsapp'));

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        }

        $request->merge([
            'whatsapp' => $digits,
            'instagram' => $this->handle($request->input('instagram')),
        ]);

        $data = $request->validate([
            'whatsapp' => ['required', 'regex:/^08\d{7,12}$/'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._]+$/'],
        ], [
            'whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 dan berisi 9–14 angka.',
            'instagram.regex' => 'Isi nama pengguna Instagram saja, misalnya avenma.properti.',
        ]);

        $setting = SiteSetting::current();
        $setting->fill($data)->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Pengaturan disimpan.');
    }

    /**
     * Accepts "@avenma", "avenma", or a full profile URL and stores the handle.
     */
    private function handle(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = preg_replace('#^https?://(www\.)?instagram\.com/#i', '', $value);

        return ltrim(trim($value, '/'), '@');
    }
}
