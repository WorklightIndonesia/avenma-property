<?php

/*
 * Pesan validasi bahasa Indonesia. Kunci yang tidak ada di sini memakai
 * cadangan bahasa Inggris dari vendor, jadi aman menambah aturan bertahap.
 */

return [

    'accepted' => ':Attribute harus disetujui.',
    'array' => ':Attribute harus berupa daftar.',
    'boolean' => ':Attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Password saat ini salah.',
    'date' => ':Attribute harus berupa tanggal yang valid.',
    'date_format' => 'Format :attribute harus :format.',
    'different' => ':Attribute dan :other harus berbeda.',
    'digits' => ':Attribute harus :digits digit.',
    'distinct' => ':Attribute berisi nilai yang sama.',
    'email' => 'Format :attribute tidak valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'file' => ':Attribute harus berupa berkas.',
    'filled' => ':Attribute wajib diisi.',
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa angka bulat.',
    'mimes' => ':Attribute harus berupa berkas bertipe: :values.',
    'mimetypes' => ':Attribute harus berupa berkas bertipe: :values.',
    'not_in' => ':Attribute yang dipilih tidak valid.',
    'numeric' => ':Attribute harus berupa angka.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':Attribute wajib diisi.',
    'required_if' => ':Attribute wajib diisi bila :other bernilai :value.',
    'required_with' => ':Attribute wajib diisi bila :values ada.',
    'same' => ':Attribute harus sama dengan :other.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah dipakai.',
    'uploaded' => ':Attribute gagal diunggah.',
    'url' => 'Format :attribute tidak valid.',

    'between' => [
        'array' => ':Attribute harus berisi :min sampai :max item.',
        'file' => 'Ukuran :attribute harus antara :min sampai :max kilobyte.',
        'numeric' => ':Attribute harus antara :min sampai :max.',
        'string' => ':Attribute harus antara :min sampai :max karakter.',
    ],

    'max' => [
        'array' => ':Attribute maksimal :max item.',
        'file' => 'Ukuran :attribute maksimal :max kilobyte.',
        'numeric' => ':Attribute maksimal :max.',
        'string' => ':Attribute maksimal :max karakter.',
    ],

    'min' => [
        'array' => ':Attribute minimal :min item.',
        'file' => 'Ukuran :attribute minimal :min kilobyte.',
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],

    'password' => [
        'letters' => ':Attribute harus memuat minimal satu huruf.',
        'mixed' => ':Attribute harus memuat huruf besar dan huruf kecil.',
        'numbers' => ':Attribute harus memuat minimal satu angka.',
        'symbols' => ':Attribute harus memuat minimal satu simbol.',
        'uncompromised' => ':Attribute pernah muncul di kebocoran data. Pilih :attribute lain.',
    ],

    'size' => [
        'array' => ':Attribute harus berisi :size item.',
        'file' => 'Ukuran :attribute harus :size kilobyte.',
        'numeric' => ':Attribute harus :size.',
        'string' => ':Attribute harus :size karakter.',
    ],

    'attributes' => [
        'address' => 'Alamat',
        'bathrooms' => 'Kamar mandi',
        'bedrooms' => 'Kamar tidur',
        'building_area' => 'Luas bangunan',
        'carport' => 'Carport',
        'contact_phone' => 'Nomor WhatsApp',
        'current_password' => 'Password saat ini',
        'description' => 'Deskripsi',
        'developer_id' => 'Developer',
        'email' => 'Email',
        'images.*.alt' => 'Alt foto',
        'images.*.label' => 'Label foto',
        'images.*.sort_order' => 'Urutan foto',
        'is_featured' => 'Penanda unggulan',
        'is_published' => 'Status terbit',
        'land_area' => 'Luas tanah',
        'floors' => 'Jumlah lantai',
        'latitude' => 'Latitude',
        'listing_type' => 'Jenis penawaran',
        'location_id' => 'Kecamatan',
        'longitude' => 'Longitude',
        'name' => 'Nama',
        'password' => 'Password',
        'password_confirmation' => 'Konfirmasi password',
        'phone' => 'Nomor telepon',
        'photos' => 'Foto',
        'photos.*' => 'Foto',
        'price' => 'Harga',
        'property_type' => 'Tipe properti',
        'role' => 'Role',
        'status' => 'Status',
        'title' => 'Judul',
    ],

];
