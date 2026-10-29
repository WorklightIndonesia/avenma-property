<?php

namespace Tests\Feature\Admin;

use App\Models\Developer;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeveloperSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_search_matches_part_of_the_name(): void
    {
        Developer::create(['name' => 'Ciputra', 'slug' => 'ciputra']);
        Developer::create(['name' => 'Summarecon', 'slug' => 'summarecon']);

        $this->actingAs($this->admin())
            ->get('/admin/developers?nama=putra')
            ->assertOk()
            ->assertSee('Ciputra')
            ->assertDontSee('Summarecon');
    }

    public function test_listing_count_sorts_both_ways(): void
    {
        $many = Developer::create(['name' => 'Banyak', 'slug' => 'banyak']);
        $few = Developer::create(['name' => 'Sedikit', 'slug' => 'sedikit']);
        Developer::create(['name' => 'Kosong', 'slug' => 'kosong']);

        $this->listing($many);
        $this->listing($many);
        $this->listing($few);

        $this->actingAs($this->admin())
            ->get('/admin/developers?urut=tersedikit')
            ->assertSeeInOrder(['Kosong', 'Sedikit', 'Banyak']);

        $this->actingAs($this->admin())
            ->get('/admin/developers?urut=terbanyak')
            ->assertSeeInOrder(['Banyak', 'Sedikit', 'Kosong']);
    }

    public function test_name_and_sort_combine(): void
    {
        $first = Developer::create(['name' => 'Grup Satu', 'slug' => 'grup-satu']);
        $second = Developer::create(['name' => 'Grup Dua', 'slug' => 'grup-dua']);
        Developer::create(['name' => 'Lain', 'slug' => 'lain']);

        $this->listing($second);
        $this->listing($second);
        $this->listing($first);

        $this->actingAs($this->admin())
            ->get('/admin/developers?nama=Grup&urut=terbanyak')
            ->assertSeeInOrder(['Grup Dua', 'Grup Satu'])
            ->assertDontSee('Lain');
    }

    public function test_unknown_sort_falls_back_to_name_order(): void
    {
        Developer::create(['name' => 'Beta', 'slug' => 'beta']);
        Developer::create(['name' => 'Alpha', 'slug' => 'alpha']);

        $this->actingAs($this->admin())
            ->get('/admin/developers?urut=acak')
            ->assertSeeInOrder(['Alpha', 'Beta']);
    }

    public function test_empty_result_explains_the_filter(): void
    {
        Developer::create(['name' => 'Ciputra', 'slug' => 'ciputra']);

        $this->actingAs($this->admin())
            ->get('/admin/developers?nama=zzz')
            ->assertSee('Tidak ada developer yang cocok.');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function listing(Developer $developer): Listing
    {
        return Listing::create([
            'developer_id' => $developer->id,
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => Str::random(10),
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ]);
    }
}
