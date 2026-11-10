<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * The legal document used to be free text with a datalist. It becomes rows
     * here so Pengaturan Listing can add, rename and remove them like any
     * option, and so the catalog filter has a fixed set to filter on.
     *
     * @var array<string, string>
     */
    private array $options = [
        'shm' => 'SHM',
        'hgb' => 'HGB',
        'shgb' => 'SHGB',
        'ajb' => 'AJB',
        'girik' => 'Girik',
        'shsrs' => 'SHSRS',
        'hpl' => 'HPL',
        'wakaf' => 'Wakaf',
    ];

    /**
     * Free text already typed by the admin, keyed by its lowercased form.
     *
     * @var array<string, string>
     */
    private array $legacy = [
        'shm' => 'shm',
        'surat hak milik' => 'shm',
        'sertifikat hak milik' => 'shm',
        'hgb' => 'hgb',
        'shgb' => 'shgb',
        'ajb' => 'ajb',
        'akta jual beli' => 'ajb',
        'girik' => 'girik',
        'shsrs' => 'shsrs',
        'hpl' => 'hpl',
        'wakaf' => 'wakaf',
    ];

    public function up(): void
    {
        $now = now();

        DB::table('listing_options')->insertOrIgnore(array_map(
            fn (string $slug, string $label) => [
                'group' => 'certificate',
                'label' => $label,
                'slug' => $slug,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            array_keys($this->options),
            $this->options,
        ));

        $values = DB::table('listings')
            ->whereNotNull('certificate')
            ->distinct()
            ->pluck('certificate');

        foreach ($values as $value) {
            $value = (string) $value;
            $slug = $this->legacy[mb_strtolower(trim($value))] ?? Str::slug($value);

            if ($slug === '' || $slug === $value) {
                continue;
            }

            // A value nobody planned for keeps its own option row instead of
            // being thrown away, so the listing still saves.
            if (! array_key_exists($slug, $this->options)) {
                DB::table('listing_options')->insertOrIgnore([[
                    'group' => 'certificate',
                    'label' => $value,
                    'slug' => $slug,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]]);
            }

            DB::table('listings')->where('certificate', $value)->update(['certificate' => $slug]);
        }
    }

    public function down(): void
    {
        foreach ($this->options as $slug => $label) {
            DB::table('listings')->where('certificate', $slug)->update(['certificate' => $label]);
        }

        DB::table('listing_options')->where('group', 'certificate')->delete();
    }
};
