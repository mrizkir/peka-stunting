<?php

namespace App\Support;

class BreastfeedingSuccessDefaults
{
	/**
	 * Kuesioner cek keberhasilan menyusui (10 pertanyaan, skor Ya = 1).
	 *
	 * @return array<string, mixed>
	 */
	public static function calculatorConfig(): array
	{
		return [
			'risk_yes_threshold' => 8,
			'questions' => [
				[
					'id' => 'feeding_frequency',
					'text' => 'Apakah bayi menyusu minimal 8-12 kali dalam 24 jam?',
				],
				[
					'id' => 'position_latch',
					'text' => 'Apakah bayi menyusu dengan posisi dan perlekatan yang benar?',
				],
				[
					'id' => 'swallowing_sound',
					'text' => 'Apakah terdengar suara menelan saat bayi menyusu?',
				],
				[
					'id' => 'softer_breast',
					'text' => 'Apakah payudara terasa lebih lunak setelah menyusui?',
				],
				[
					'id' => 'satisfied_calm',
					'text' => 'Apakah bayi tampak puas dan tenang setelah menyusui?',
				],
				[
					'id' => 'wet_diapers',
					'text' => 'Apakah bayi BAK minimal 6 kali dalam 24 jam (setelah usia 5 hari)?',
				],
				[
					'id' => 'clear_urine',
					'text' => 'Apakah warna urin bayi jernih (tidak kuning pekat)?',
				],
				[
					'id' => 'bowel_movements',
					'text' => 'Apakah bayi BAB minimal 3-4 kali dalam 24 jam dengan tekstur lembek/cair kekuningan (setelah usia 4 hari)?',
				],
				[
					'id' => 'birth_weight_regained',
					'text' => 'Apakah berat badan bayi kembali ke berat lahir pada usia 10-14 hari?',
				],
				[
					'id' => 'weight_gain_curve',
					'text' => 'Apakah kenaikan berat badan bayi sesuai dengan kurva pertumbuhan?',
				],
			],
		];
	}
}
