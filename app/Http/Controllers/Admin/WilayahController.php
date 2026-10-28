<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\PhotonAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Feeds the two typed pickers on the listing form. The region lists are read
 * from the database on demand: all 7,285 kecamatan would be a heavy payload on
 * a phone, and the agent only ever needs one province at a time.
 */
class WilayahController extends Controller
{
    public function wilayah(Request $request): JsonResponse
    {
        $level = (string) $request->query('level');
        $province = trim((string) $request->query('province'));
        $city = trim((string) $request->query('city'));

        $options = match ($level) {
            'province' => Location::query()->distinct()->orderBy('province')->pluck('province'),
            'city' => $province === '' ? null : Location::query()
                ->where('province', $province)->distinct()->orderBy('city')->pluck('city'),
            'district' => $province === '' || $city === '' ? null : Location::query()
                ->where('province', $province)->where('city', $city)->orderBy('name')->get(['id', 'name']),
            default => null,
        };

        if ($options === null) {
            return response()->json(['options' => []], 422);
        }

        return response()->json(['options' => $options]);
    }

    public function alamat(Request $request, PhotonAddress $photon): JsonResponse
    {
        return response()->json(['results' => $photon->search((string) $request->query('q'))]);
    }
}
