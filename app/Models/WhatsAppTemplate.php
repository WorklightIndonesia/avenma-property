<?php

namespace App\Models;

use App\Support\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class WhatsAppTemplate extends Model
{
    use RecordsActivity;

    /**
     * The fixed set of templates and the placeholders each one understands.
     * The key is the contract with the public pages, so the admin screen may
     * rewrite the text but never add or remove a row.
     */
    public const TEMPLATES = [
        'listing_inquiry' => [
            'label' => 'Tanya properti',
            'hint' => 'Tombol WhatsApp dan tombol Konsultasi KPR di halaman properti.',
            'placeholders' => ['{judul}', '{url}'],
        ],
        'listing_visit' => [
            'label' => 'Jadwal kunjungan',
            'hint' => 'Form "Jadwalkan kunjungan" di halaman properti.',
            'placeholders' => ['{nama}', '{judul}', '{tanggal}'],
        ],
        'layanan_default' => [
            'label' => 'Layanan umum',
            'hint' => 'Tombol pembuka halaman Layanan, dan layanan yang pesannya dikosongkan.',
            'placeholders' => [],
        ],
        'sister_virtual_office' => [
            'label' => 'Virtual Office',
            'hint' => 'Menu dan footer, serta baris Layanan lain di halaman Layanan.',
            'placeholders' => [],
        ],
        'sister_it_solutions' => [
            'label' => 'IT & Business Solutions',
            'hint' => 'Menu dan footer, serta baris Layanan lain di halaman Layanan.',
            'placeholders' => [],
        ],
    ];

    protected $fillable = [
        'key',
        'message',
    ];

    /** Eloquent would split the name into whats_app_templates. */
    protected $table = 'whatsapp_templates';

    /**
     * The stored text with its placeholders filled in. A key missing from the
     * table (deleted by hand) yields an empty message instead of an error.
     *
     * @param  array<string, string>  $vars
     */
    public static function message(string $key, array $vars = []): string
    {
        $message = (string) static::query()->where('key', $key)->value('message');

        foreach ($vars as $name => $value) {
            $message = str_replace('{'.$name.'}', (string) $value, $message);
        }

        return $message;
    }
}
