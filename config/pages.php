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
            'hero' => ['label' => 'Bagian pembuka', 'image' => true, 'cta' => 2],
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

    'layanan' => [
        'label' => 'Layanan',
        'path' => '/layanan',
        'route' => 'public.layanan.index',
        'sections' => [
            // The button opens WhatsApp with a fixed template, so only its
            // label is editable here.
            'hero' => ['label' => 'Bagian pembuka', 'cta' => 1, 'cta_url' => false],
        ],
    ],
];
