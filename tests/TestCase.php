<?php

namespace Tests;

use App\Models\Location;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    /**
     * A region row in the shape the seeders write: `name` is the kecamatan,
     * with its kota/kabupaten and province around it.
     */
    protected function location(
        string $name = 'Pondok Aren',
        string $city = 'Kota Tangerang Selatan',
        string $province = 'Banten',
    ): Location {
        return Location::firstOrCreate(
            ['name' => $name, 'city' => $city, 'province' => $province],
            ['slug' => Str::slug($province.' '.$city.' '.$name)],
        );
    }
}
