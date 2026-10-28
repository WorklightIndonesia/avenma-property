<?php

namespace Tests\Feature;

use App\Services\PhotonAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PhotonAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_result_is_matched_to_the_official_region_rows(): void
    {
        $location = $this->location('Pondok Aren', 'Kota Tangerang Selatan', 'Banten');

        Http::fake(['photon.komoot.io/*' => Http::response(['features' => [$this->feature()]])]);

        $result = app(PhotonAddress::class)->search('bintaro jaya');

        $this->assertCount(1, $result);
        $this->assertSame('Banten', $result[0]['province']);
        $this->assertSame('Kota Tangerang Selatan', $result[0]['city']);
        $this->assertSame('Pondok Aren', $result[0]['district']);
        $this->assertSame($location->id, $result[0]['location_id']);
        $this->assertSame(-6.2615, $result[0]['lat']);
        $this->assertSame(106.8, $result[0]['lng']);
    }

    public function test_results_outside_indonesia_are_dropped(): void
    {
        Http::fake(['photon.komoot.io/*' => Http::response(['features' => [
            $this->feature(['countrycode' => 'MY']),
            $this->feature(['countrycode' => 'ID']),
        ]])]);

        $this->assertCount(1, app(PhotonAddress::class)->search('bintaro'));
    }

    public function test_a_repeated_district_name_is_not_guessed(): void
    {
        $this->location('Sukamaju', 'Kota Tangerang', 'Banten');
        $this->location('Sukamaju', 'Kota Tangerang Selatan', 'Banten');

        // No city in the result, so the name matches two kota: the district
        // stays empty for the agent to pick, but the province is still known.
        Http::fake(['photon.komoot.io/*' => Http::response(['features' => [
            $this->feature(['city' => null, 'district' => 'Sukamaju']),
        ]])]);

        $result = app(PhotonAddress::class)->search('sukamaju');

        $this->assertSame('Banten', $result[0]['province']);
        $this->assertNull($result[0]['city']);
        $this->assertNull($result[0]['district']);
        $this->assertNull($result[0]['location_id']);
    }

    public function test_a_timeout_returns_no_results(): void
    {
        Http::fake(fn () => throw new ConnectionException('timed out'));

        $this->assertSame([], app(PhotonAddress::class)->search('bintaro'));
    }

    public function test_a_successful_search_is_cached(): void
    {
        Http::fake(['photon.komoot.io/*' => Http::response(['features' => [$this->feature()]])]);

        $photon = app(PhotonAddress::class);
        $photon->search('bintaro');
        $photon->search('bintaro');

        Http::assertSentCount(1);
    }

    public function test_a_failure_is_not_cached(): void
    {
        Http::fakeSequence('photon.komoot.io/*')
            ->push('', 503)
            ->push(['features' => [$this->feature()]]);

        $this->assertSame([], app(PhotonAddress::class)->search('bintaro'));
        $this->assertCount(1, app(PhotonAddress::class)->search('bintaro'));
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    private function feature(array $properties = []): array
    {
        return [
            'geometry' => ['coordinates' => [106.8, -6.2615]],
            'properties' => array_merge([
                'name' => 'Bintaro Jaya',
                'street' => 'Jalan Bintaro Utama',
                'housenumber' => '12',
                'district' => 'Pondok Aren',
                'city' => 'South Tangerang',
                'state' => 'Banten',
                'countrycode' => 'ID',
            ], $properties),
        ];
    }
}
