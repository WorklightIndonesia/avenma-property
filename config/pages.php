<?php

/*
| The approved skeleton of every public page. Admins change the copy inside a
| section, never the order or the styling, so a page cannot drift away from
| the layout the client approved.
|
| label   name shown in the admin screen
| image   opens an upload field (hero only for now)
| cta     how many buttons the section renders and the admin may edit (0–2)
*/

return [
    'home' => [
        'label' => 'Beranda',
        'path' => '/',
        'route' => 'public.pages.home',
        'sections' => [
            'hero' => ['label' => 'Hero', 'image' => true, 'cta' => 2],
        ],
    ],

    'tentang-kami' => [
        'label' => 'Tentang Kami',
        'path' => '/tentang-kami',
        'route' => 'public.pages.about',
        'sections' => [
            'hero' => ['label' => 'Hero', 'image' => true, 'cta' => 2],
            'cerita' => ['label' => 'Cerita Avenma'],
            'nilai' => ['label' => 'Yang kami pegang'],
            'cta' => ['label' => 'Ajakan di akhir halaman', 'cta' => 1],
        ],
    ],

    'kontak' => [
        'label' => 'Kontak',
        'path' => '/kontak',
        'route' => 'public.pages.contact',
        'sections' => [
            'hero' => ['label' => 'Hero', 'image' => true, 'cta' => 2],
            'kontak' => ['label' => 'Keterangan kontak'],
        ],
    ],

    'syarat-ketentuan' => [
        'label' => 'Syarat & Ketentuan',
        'path' => '/syarat-ketentuan',
        'route' => 'public.pages.terms',
        'sections' => [
            'hero' => ['label' => 'Hero', 'image' => true, 'cta' => 2],
            'ketentuan' => ['label' => 'Isi ketentuan'],
        ],
    ],
];
