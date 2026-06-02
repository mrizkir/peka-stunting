<?php

namespace App\Support;

class EducationMenuDescriptions
{
	/**
	 * Default deskripsi menu (ditampilkan di aplikasi mobile sebelum daftar section).
	 */
	public static function defaults(): array
	{
		return [
			'info-aplikasi' => 'Tentang aplikasi PEKA Stunting.',
			'mengenal-stunting' => 'Konten dasar untuk memahami stunting dan dampaknya.',
			'remaja-putri' => implode("\n\n", [
				implode("\n", [
					'Pergi menjaring ke Pulau Penyengat,',
					'Dapatlah se ekor ikan tenggiri.',
					'Wahai budak dara belajarlah semangat',
					'Cegah stunting sedari dini',
				]),
				'Hai Remaja Putri, Selamat Datang...',
				'Remaja putri sebagai calon ibu yang menentukan kesehatan generasi masa depan sangat penting lho melakukan deteksi dini dan pencegahan stunting. Yuk lakukan deteksi dini dan tips trik cara mencegah stunting.',
			]),
			'calon-pengantin' => 'Persiapan kesehatan sebelum kehamilan dan 1000 HPK.',
			'ibu-hamil' => 'Panduan pemeriksaan, nutrisi, dan pencegahan risiko.',
			'ibu-nifas-dan-menyusui' => 'Materi laktasi, gizi, dan pemulihan ibu pasca persalinan.',
			'bayi-dan-balita' => 'Pemantauan status gizi, ASI, MPASI, dan imunisasi.',
		];
	}

	public static function forSlug(string $slug): ?string
	{
		$description = self::defaults()[$slug] ?? null;

		return filled($description) ? $description : null;
	}
}
