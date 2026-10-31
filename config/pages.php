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

    'kontak' => [
        'label' => 'Kontak',
        'path' => '/kontak',
        'route' => 'public.pages.contact',
        'sections' => [
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
