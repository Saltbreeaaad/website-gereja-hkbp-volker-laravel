<?php

/*
|--------------------------------------------------------------------------
| Identitas & Profil Gereja
|--------------------------------------------------------------------------
|
| Satu sumber kebenaran untuk data gereja yang dipakai berulang di layout,
| meta SEO, structured data (JSON-LD), dan footer. Ubah di sini saja — tidak
| perlu menyisir Blade satu per satu.
|
*/

return [

    'nama' => 'HKBP Persiapan Resort Volker',
    'nama_pendek' => 'HKBP Volker',
    'denominasi' => 'Huria Kristen Batak Protestan',

    'slogan' => 'Menjadi Gereja yang Inklusif, Dialogis, dan Menjadi Berkat',

    'deskripsi' => 'Website resmi HKBP Persiapan Resort Volker, Tanjung Priok, '
        .'Jakarta Utara. Jadwal ibadah, renungan harian, warta jemaat, laporan '
        .'kas gereja, galeri kegiatan, dan permohonan penggunaan gedung gereja.',

    'alamat' => [
        'jalan' => 'Jl. Volker Raya No. 1, RT 01/RW 02',
        'kelurahan' => 'Tanjung Priok',
        'kota' => 'Jakarta Utara',
        'provinsi' => 'DKI Jakarta',
        'kode_pos' => '14310',
        'negara' => 'ID',
    ],

    'telepon' => '(021) 12345678',
    'email' => null,

    'koordinat' => [
        'lat' => -6.124934,
        'lng' => 106.877028,
    ],

    'peta_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.04278453448'
        .'!2d106.87702831411586!3d-6.12493399556531!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1'
        .'!3m3!1m2!1s0x2e6a1ff5a4321303%3A0xc48c1488c5750d5f!2sTanjung%20Priok%2C%20North'
        .'%20Jakarta%20City%2C%20Jakarta!5e0!3m2!1sen!2sid!4v1620000000000!5m2!1sen!2sid',

    /*
    | Menu navigasi utama. Dipakai oleh navbar desktop, panel menu mobile,
    | dan footer sekaligus — supaya ketiganya tidak pernah lagi berbeda isi.
    */
    /*
    | Sakelar cache isi halaman publik. Cache dibatalkan sendiri setiap kali
    | pengurus menyunting data, jadi normalnya tidak perlu disentuh; setel
    | GEREJA_CACHE_KONTEN=false hanya saat menelusuri dugaan data basi.
    */
    'cache_konten' => (bool) env('GEREJA_CACHE_KONTEN', true),

    'menu' => [
        ['route' => 'home', 'label' => 'Beranda'],
        ['route' => 'profil', 'label' => 'Profil'],
        ['route' => 'pelayan', 'label' => 'Pelayan'],
        ['route' => 'renungan', 'label' => 'Renungan'],
        ['route' => 'warta', 'label' => 'Warta'],
        ['route' => 'galeri', 'label' => 'Galeri'],
        ['route' => 'penggunaan-gereja', 'label' => 'Penggunaan Gereja'],
    ],

];
