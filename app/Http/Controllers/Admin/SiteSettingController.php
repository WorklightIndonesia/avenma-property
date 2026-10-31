<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

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
            // A share link cannot be embedded; only Google Maps links are fetched
            // server-side to read the place coordinates for the contact map.
            'maps_url' => ['nullable', 'url:https', 'max:2048', 'regex:/^https:\/\/(maps\.app\.goo\.gl\/|goo\.gl\/maps\/|maps\.google\.[a-z.]+\/|(www\.)?google\.[a-z.]+\/maps\/)/i'],
            'instagram' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._]+$/'],
        ], [
            'whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 dan berisi 9–14 angka.',
            'maps_url.url' => 'Tautan Google Maps harus alamat https:// yang valid.',
            'maps_url.regex' => 'Tautan harus dari Google Maps, misalnya https://maps.app.goo.gl/…',
            'instagram.regex' => 'Isi nama pengguna Instagram saja, misalnya avenma.properti.',
        ]);

        $setting = SiteSetting::current();

        $data['maps_lat'] = $setting->maps_lat;
        $data['maps_lng'] = $setting->maps_lng;

        if (($data['maps_url'] ?? null) !== $setting->maps_url
            || (filled($data['maps_url'] ?? null) && $setting->maps_lat === null)) {
            [$data['maps_lat'], $data['maps_lng']] = $this->coordinates($data['maps_url'] ?? null);
        }

        $setting->fill($data)->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Pengaturan disimpan.');
    }

    /**
     * Reads the place coordinates from a Maps link, following a short link once.
     * Nulls leave the contact map centered on the office address instead.
     *
     * @return array{0: float|null, 1: float|null}
     */
    private function coordinates(?string $url): array
    {
        if (blank($url)) {
            return [null, null];
        }

        $direct = $this->parseCoordinates($url);

        if ($direct !== null) {
            return $direct;
        }

        try {
            $final = (string) Http::timeout(5)->get($url)->effectiveUri();
        } catch (Throwable) {
            return [null, null];
        }

        return $this->parseCoordinates($final) ?? [null, null];
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    private function parseCoordinates(string $url): ?array
    {
        foreach (['/@(-?\d+\.\d+),(-?\d+\.\d+)/', '/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/'] as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return [(float) $matches[1], (float) $matches[2]];
            }
        }

        return null;
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
