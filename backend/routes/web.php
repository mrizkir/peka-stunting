<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $educationMenus = [
        [
            'title' => 'Mengenal Stunting',
            'slug' => 'mengenal-stunting',
            'description' => 'Konten dasar untuk memahami stunting dan dampaknya.',
            'items_count' => 5,
        ],
        [
            'title' => 'Remaja Putri',
            'slug' => 'remaja-putri',
            'description' => 'Deteksi dini dan upaya kesehatan untuk remaja putri.',
            'items_count' => 11,
        ],
        [
            'title' => 'Calon Pengantin',
            'slug' => 'calon-pengantin',
            'description' => 'Persiapan kesehatan sebelum kehamilan dan 1000 HPK.',
            'items_count' => 9,
        ],
        [
            'title' => 'Ibu Hamil',
            'slug' => 'ibu-hamil',
            'description' => 'Panduan pemeriksaan, nutrisi, dan pencegahan risiko.',
            'items_count' => 9,
        ],
        [
            'title' => 'Ibu Nifas dan Menyusui',
            'slug' => 'ibu-nifas-dan-menyusui',
            'description' => 'Materi laktasi, gizi, dan pemulihan ibu pasca persalinan.',
            'items_count' => 12,
        ],
        [
            'title' => 'Bayi dan Balita',
            'slug' => 'bayi-dan-balita',
            'description' => 'Pemantauan status gizi, ASI, MPASI, dan imunisasi.',
            'items_count' => 8,
        ],
    ];

    $recentContents = [
        [
            'title' => 'Pengertian Stunting',
            'menu' => 'Mengenal Stunting',
            'status' => 'Published',
            'updated_at' => 'Hari ini',
        ],
        [
            'title' => 'Cek IMT',
            'menu' => 'Remaja Putri',
            'status' => 'Draft',
            'updated_at' => 'Menunggu rumus final',
        ],
        [
            'title' => 'Cara Menyusui yang Benar',
            'menu' => 'Ibu Nifas dan Menyusui',
            'status' => 'Published',
            'updated_at' => '2 hari lalu',
        ],
    ];

    return view('dashboard', compact('educationMenus', 'recentContents'));
});

Route::get('/education', function () {
    $menu = [
        'title' => 'Remaja Putri',
        'description' => 'Template daftar submenu dan konten edukasi dengan struktur tetap.',
        'sections' => [
            [
                'title' => 'Deteksi Dini',
                'items' => [
                    ['title' => 'Cek IMT', 'type' => 'Kalkulator', 'status' => 'Draft'],
                    ['title' => 'Cek LILA', 'type' => 'Kalkulator', 'status' => 'Draft'],
                    ['title' => 'Cek Risiko Anemia', 'type' => 'Kalkulator', 'status' => 'Draft'],
                ],
            ],
            [
                'title' => 'Upaya Kesehatan',
                'items' => [
                    ['title' => 'Pola Gizi Seimbang', 'type' => 'Konten', 'status' => 'Published'],
                    ['title' => 'Cara Cegah Anemia', 'type' => 'Konten', 'status' => 'Published'],
                    ['title' => 'Olahraga Rutin', 'type' => 'Konten', 'status' => 'Published'],
                ],
            ],
        ],
    ];

    return view('education.index', compact('menu'));
})->name('education.index');

Route::get('/education/content', function () {
    $content = [
        'menu' => 'Remaja Putri',
        'section' => 'Deteksi Dini',
        'title' => 'Cek IMT',
        'status' => 'Draft',
        'summary' => 'Halaman ini disiapkan sebagai template kalkulator IMT dengan Tailwind.',
        'body' => "Template ini akan menampung form input, area hasil, dan penjelasan interpretasi.\nRumus final akan dipasang setelah angka dan kategorinya disetujui client.",
        'notes' => [
            'Gunakan slug yang konsisten untuk route dan seeder.',
            'Komponen form dan kartu hasil dipakai ulang oleh kalkulator lain.',
            'Bagian aksi admin disiapkan untuk mode edit konten.',
        ],
    ];

    return view('education.show', compact('content'));
})->name('education.show');
