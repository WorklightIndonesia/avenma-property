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
        return view('admin.settings.edit', ['setting' => SiteSetting::query()->first() ?? new SiteSetting]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Stored as 08…; accept 62… or +62… and strip spaces and dashes.
        $digits = preg_replace('/\D+/', '', (string) $request->input('whatsapp'));

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        }

        $request->merge(['whatsapp' => $digits]);

        $data = $request->validate([
            'whatsapp' => ['required', 'regex:/^08\d{7,12}$/'],
        ], [
            'whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 dan berisi 9–14 angka.',
        ]);

        $setting = SiteSetting::query()->first() ?? new SiteSetting;
        $setting->fill($data)->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Pengaturan disimpan.');
    }
}
