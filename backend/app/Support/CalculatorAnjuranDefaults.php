<?php

namespace App\Support;

use App\Models\CalculatorAnjuranRule;

class CalculatorAnjuranDefaults
{
	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function bmiRules(): array
	{
		return [
			[
				'sort_order' => 1,
				'metric' => CalculatorAnjuranRule::METRIC_BMI,
				'indicator' => null,
				'threshold' => 30.0,
				'operator' => CalculatorAnjuranRule::OPERATOR_GT,
				'is_default' => false,
				'label' => 'Obesitas',
				'slug' => 'obese',
				'anjuran' => 'Bagi remaja dengan IMT kategori obesitas (IMT 30 atau lebih), tujuannya bukan sekadar menurunkan berat badan secara drastis , melainkan memperlambat laju kenaikan berat badan sambil membiarkan tinggi badan bertambah, serta memperbaiki pola hidup yang lebih baik seperti tidur yang teratur, pastikan minum air putih 2 liter per hari, tidur 8 – 10 jam/per hari, tingkatkan aktivitas fisik dan cek rutin IMT secara berkala ya',
			],
			[
				'sort_order' => 2,
				'metric' => CalculatorAnjuranRule::METRIC_BMI,
				'indicator' => null,
				'threshold' => 25.0,
				'operator' => CalculatorAnjuranRule::OPERATOR_GT,
				'is_default' => false,
				'label' => 'Gemuk',
				'slug' => 'overweight',
				'anjuran' => 'Bagi remaja dengan IMT kategori gemuk (overweight), tujuannya bukan sekadar menurunkan berat badan secara drastis , melainkan memperlambat laju kenaikan berat badan sambil membiarkan tinggi badan bertambah, serta memperbaiki pola hidup yang lebih baik seperti tidur yang teratur, pastikan minum air putih 2 liter per hari, tidur 8 – 10 jam/per hari, tingkatkan aktivitas fisik dan cek rutin IMT secara berkala ya',
			],
			[
				'sort_order' => 3,
				'metric' => CalculatorAnjuranRule::METRIC_BMI,
				'indicator' => null,
				'threshold' => 18.5,
				'operator' => CalculatorAnjuranRule::OPERATOR_GT,
				'is_default' => false,
				'label' => 'Normal',
				'slug' => 'normal',
				'anjuran' => 'Selamat anda dalam kondisi Normal. Bagi remaja dengan IMT normal (berada di rentang 18,5 hingga 25,0), fokus utamanya adalah pemeliharaan dan menjaga komposisi tubuh agar tetap sehat selama masa pertumbuhan dengan cara pertahankan kualitas nutrisi makanan, olahraga yang rutin, pastikan minum air putih 2 liter per hari, tidur 8 – 10 jam/per hari, dan cek rutin secara berkala ya.',
			],
			[
				'sort_order' => 4,
				'metric' => CalculatorAnjuranRule::METRIC_BMI,
				'indicator' => null,
				'threshold' => null,
				'operator' => CalculatorAnjuranRule::OPERATOR_GT,
				'is_default' => true,
				'label' => 'Kurus',
				'slug' => 'underweight',
				'anjuran' => 'Bagi remaja dengan kategori kurus (IMT di bawah 18,5), langkah utama yang dianjurkan adalah meningkatkan asupan kalori dan nutrisi secara sehat, kebiasaan minum yang tepat, tidur yang teratur, melakukan latihan fisik untuk membangun massa otot dan cek rutin IMT secara berkala ya. Lakukan konsultasi kesehatan di Puskesmas atau fasilitas kesehatan lainnya.',
			],
		];
	}

	/**
	 * Aturan LILA flat (menu selain remaja putri): ambang 23,5 cm.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function lilaRules(): array
	{
		return [
			[
				'sort_order' => 1,
				'metric' => CalculatorAnjuranRule::METRIC_LILA_CM,
				'indicator' => null,
				'threshold' => 23.5,
				'operator' => CalculatorAnjuranRule::OPERATOR_GTE,
				'is_default' => false,
				'label' => 'Selamat, status gizi relatif normal',
				'slug' => 'normal',
				'anjuran' => 'Jika hasil Lingkar Lengan Atas (LiLA) normal (≥ 23,5 cm), artinya cadangan lemak dan massa otot tubuh saat ini dalam kondisi cukup. Fokus utamanya adalah menjaga agar status gizi tetap stabil dan tidak jatuh ke risiko KEK atau sebaliknya menjadi obesitas. Pertahankan pola makan gizi seimbang dan aktifitas fisik secara rutin.',
			],
			[
				'sort_order' => 2,
				'metric' => CalculatorAnjuranRule::METRIC_LILA_CM,
				'indicator' => null,
				'threshold' => null,
				'operator' => CalculatorAnjuranRule::OPERATOR_GTE,
				'is_default' => true,
				'label' => 'Anda berisiko kekurangan gizi (KEK)',
				'slug' => 'at_risk',
				'anjuran' => 'Kekurangan Energi Kronis (KEK), yang biasanya ditandai dengan ukuran Lingkar Lengan Atas (LiLA) kurang dari 23,5 cm, merupakan kondisi serius karena menunjukkan kekurangan gizi jangka panjang yang bisa menghambat pertumbuhan dan menurunkan sistem imun. Terus apa yang harus dilakukan? Tingkatkan konsumsi protein kualitas tinggi seperti telur, ikan, ayam, daging, dan susu untuk memperbaiki jaringan tubuh dan meningkatkan massa otot. Selain makan besar 3 kali sehari, sangat disarankan mengonsumsi makanan tambahan padat gizi (seperti biskuit khusus dari puskesmas, kacang hijau, atau telur rebus) di antara waktu makan. Lakukan konsultasi kesehatan di Puskesmas atau fasilitas kesehatan lainnya.',
			],
		];
	}

	/**
	 * Aturan LILA remaja putri berdasarkan kelompok usia.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function lilaRulesRemajaPutri(): array
	{
		$rules = [];
		$sortOrder = 1;

		foreach (
			[
				[CalculatorAnjuranRule::INDICATOR_AGE_10_14, 18.5],
				[CalculatorAnjuranRule::INDICATOR_AGE_15_17, 22.0],
				[CalculatorAnjuranRule::INDICATOR_AGE_GT_17, 23.5],
			] as [$indicator, $threshold]
		) {
			foreach (self::lilaAgeBandRules($indicator, $threshold) as $rule) {
				$rules[] = [...$rule, 'sort_order' => $sortOrder++];
			}
		}

		return $rules;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private static function lilaAgeBandRules(
		string $indicator,
		float $threshold,
	): array {
		$normalAnjuran = match ($indicator) {
			CalculatorAnjuranRule::INDICATOR_AGE_10_14 => 'Untuk remaja putri berusia 10 – 14 tahun, Jika hasil Lingkar Lengan Atas (LiLA) remaja normal (≥ 18,5 cm), artinya cadangan lemak dan massa otot tubuh saat ini dalam kondisi cukup. Fokus utamanya adalah menjaga agar status gizi tetap stabil dan tidak jatuh ke risiko KEK atau sebaliknya menjadi obesitas. Pertahankan pola makan gizi seimbang dan aktifitas fisik secara rutin.',
			CalculatorAnjuranRule::INDICATOR_AGE_15_17 => 'Untuk remaja putri berusia 15 – 17 tahun, Jika hasil Lingkar Lengan Atas (LiLA) remaja normal (≥ 22 cm), artinya cadangan lemak dan massa otot tubuh saat ini dalam kondisi cukup. Fokus utamanya adalah menjaga agar status gizi tetap stabil dan tidak jatuh ke risiko KEK atau sebaliknya menjadi obesitas. Pertahankan pola makan gizi seimbang dan aktifitas fisik secara rutin.',
			default => 'Jika Lingkar Lengan Atas (LiLA) remaja > 17 tahun menunjukan normal (≥ 23,5 cm), artinya cadangan lemak dan massa otot tubuh saat ini dalam kondisi cukup. Fokus utamanya adalah menjaga agar status gizi tetap stabil dan tidak jatuh ke risiko KEK atau sebaliknya menjadi obesitas. Pertahankan pola makan gizi seimbang dan aktifitas fisik secara rutin.',
		};

		$atRiskAnjuran = match ($indicator) {
			CalculatorAnjuranRule::INDICATOR_AGE_10_14 => 'Kekurangan Energi Kronis (KEK) pada remaja putri usia 10 -14 tahun, yang biasanya ditandai dengan ukuran Lingkar Lengan Atas (LiLA) kurang dari 18,5 cm, merupakan kondisi serius karena menunjukkan kekurangan gizi jangka panjang yang bisa menghambat pertumbuhan dan menurunkan sistem imun. Terus apa yang harus dilakukan? Remaja harus meningkatkan konsumsi protein kualitas tinggi seperti telur, ikan, ayam, daging, dan susu untuk memperbaiki jaringan tubuh dan meningkatkan massa otot. Selain makan besar 3 kali sehari, sangat disarankan mengonsumsi makanan tambahan padat gizi (seperti biskuit khusus dari puskesmas, kacang hijau, atau telur rebus) di antara waktu makan. Lakukan konsultasi kesehatan di Puskesmas atau fasilitas kesehatan lainnya.',
			CalculatorAnjuranRule::INDICATOR_AGE_15_17 => 'Kekurangan Energi Kronis (KEK) pada remaja putri usia 15 -17 tahun, yang biasanya ditandai dengan ukuran Lingkar Lengan Atas (LiLA) kurang dari 22 cm, merupakan kondisi serius karena menunjukkan kekurangan gizi jangka panjang yang bisa menghambat pertumbuhan dan menurunkan sistem imun. Terus apa yang harus dilakukan? Remaja harus meningkatkan konsumsi protein kualitas tinggi seperti telur, ikan, ayam, daging, dan susu untuk memperbaiki jaringan tubuh dan meningkatkan massa otot. Selain makan besar 3 kali sehari, sangat disarankan mengonsumsi makanan tambahan padat gizi (seperti biskuit khusus dari puskesmas, kacang hijau, atau telur rebus) di antara waktu makan. Lakukan konsultasi kesehatan di Puskesmas atau fasilitas kesehatan lainnya.',
			default => 'Kekurangan Energi Kronis (KEK) pada remaja usia  > 17 tahun, yang biasanya ditandai dengan ukuran Lingkar Lengan Atas (LiLA) kurang dari 23,5 cm, merupakan kondisi serius karena menunjukkan kekurangan gizi jangka panjang yang bisa menghambat pertumbuhan dan menurunkan sistem imun. Terus apa yang harus dilakukan? Remaja harus meningkatkan konsumsi protein kualitas tinggi seperti telur, ikan, ayam, daging, dan susu untuk memperbaiki jaringan tubuh dan meningkatkan massa otot. Selain makan besar 3 kali sehari, sangat disarankan mengonsumsi makanan tambahan padat gizi (seperti biskuit khusus dari puskesmas, kacang hijau, atau telur rebus) di antara waktu makan. Lakukan konsultasi kesehatan di Puskesmas atau fasilitas kesehatan lainnya.',
		};

		return [
			[
				'sort_order' => 1,
				'metric' => CalculatorAnjuranRule::METRIC_LILA_CM,
				'indicator' => $indicator,
				'threshold' => $threshold,
				'operator' => CalculatorAnjuranRule::OPERATOR_GTE,
				'is_default' => false,
				'label' => 'Selamat, status gizi relatif normal',
				'slug' => 'normal',
				'anjuran' => $normalAnjuran,
			],
			[
				'sort_order' => 2,
				'metric' => CalculatorAnjuranRule::METRIC_LILA_CM,
				'indicator' => $indicator,
				'threshold' => null,
				'operator' => CalculatorAnjuranRule::OPERATOR_GTE,
				'is_default' => true,
				'label' => 'Anda berisiko kekurangan gizi (KEK)',
				'slug' => 'at_risk',
				'anjuran' => $atRiskAnjuran,
			],
		];
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function anemiaRules(): array
	{
		return [
			[
				'sort_order' => 1,
				'metric' => CalculatorAnjuranRule::METRIC_YES_COUNT,
				'indicator' => null,
				'threshold' => 7.0,
				'operator' => CalculatorAnjuranRule::OPERATOR_GT,
				'is_default' => false,
				'label' => 'Resiko Tinggi Anemia',
				'slug' => 'high_risk',
				'anjuran' => 'Segera ke Puskesmas atau Fasilitas Kesehatan lainnya: Status risiko tinggi memerlukan pemeriksaan laboratorium untuk memastikan kadar Hb.',
			],
			[
				'sort_order' => 2,
				'metric' => CalculatorAnjuranRule::METRIC_YES_COUNT,
				'indicator' => null,
				'threshold' => 4.0,
				'operator' => CalculatorAnjuranRule::OPERATOR_GTE,
				'is_default' => false,
				'label' => 'Risiko Sedang Anemia',
				'slug' => 'medium_risk',
				'anjuran' => 'Perkuat Asupan Zat Besi Jangan menunggu sampai lemas. Pastikan dalam piring makan harian selalu ada minimal satu porsi daging merah, hati ayam, telur, atau ikan setiap hari karena zat besinya paling mudah diserap tubuh. Perbanyak konsumsi bayam, daun singkong, atau kangkung sebagai pendamping lauk hewani. Hindari teh dan kopi saat makan atau beri jeda 2 jam. Konsumsi buah yang kaya vitamin C. rutin minum TTD 1 tablet seminggu sekali.',
			],
			[
				'sort_order' => 3,
				'metric' => CalculatorAnjuranRule::METRIC_YES_COUNT,
				'indicator' => null,
				'threshold' => 1.0,
				'operator' => CalculatorAnjuranRule::OPERATOR_GTE,
				'is_default' => false,
				'label' => 'Risiko Rendah Anemia',
				'slug' => 'low_risk',
				'anjuran' => 'Perkuat Asupan Zat Besi Jangan menunggu sampai lemas. Pastikan dalam piring makan harian selalu ada minimal satu porsi daging merah, hati ayam, telur, atau ikan setiap hari karena zat besinya paling mudah diserap tubuh. Perbanyak konsumsi bayam, daun singkong, atau kangkung sebagai pendamping lauk hewani.',
			],
			[
				'sort_order' => 4,
				'metric' => CalculatorAnjuranRule::METRIC_YES_COUNT,
				'indicator' => null,
				'threshold' => null,
				'operator' => CalculatorAnjuranRule::OPERATOR_GTE,
				'is_default' => true,
				'label' => 'Tidak ada resiko Anemia',
				'slug' => 'normal',
				'anjuran' => 'Selamat kondisi Anda normal. Langkah selanjutnya adalah mempertahankan kondisi tersebut agar cadangan zat besi dalam tubuh tetap terjaga selama masa pertumbuhan.',
			],
		];
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function menyusuiRules(): array
	{
		return [
			[
				'sort_order' => 1,
				'metric' => CalculatorAnjuranRule::METRIC_YES_COUNT,
				'indicator' => null,
				'threshold' => 8.0,
				'operator' => CalculatorAnjuranRule::OPERATOR_GTE,
				'is_default' => false,
				'label' => 'Menyusui Berhasil',
				'slug' => 'normal',
				'anjuran' => 'Selamat! Indikator keberhasilan menyusui Anda baik. Pertahankan ASI eksklusif, susui sesuai kebutuhan bayi, dan pastikan ibu juga cukup makan dan minum.',
			],
			[
				'sort_order' => 2,
				'metric' => CalculatorAnjuranRule::METRIC_YES_COUNT,
				'indicator' => null,
				'threshold' => null,
				'operator' => CalculatorAnjuranRule::OPERATOR_GTE,
				'is_default' => true,
				'label' => 'Perlu Evaluasi dan Dukungan Menyusui',
				'slug' => 'need_follow_up',
				'anjuran' => 'Beberapa indikator keberhasilan menyusui belum terpenuhi. Segera konsultasikan ke Puskesmas atau fasilitas kesehatan untuk evaluasi dan bimbingan laktasi.',
			],
		];
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function nutritionalStatusRules(): array
	{
		return array_merge(
			self::zScoreIndicatorRules(
				CalculatorAnjuranRule::INDICATOR_HEIGHT_FOR_AGE,
				[
					['lt', -3.0, 'Sangat pendek', 'very_stunted', 'Segera rujuk ke Puskesmas/fasilitas kesehatan untuk penanganan stunting berat dan pemantauan pertumbuhan intensif.'],
					['lt', -2.0, 'Pendek (stunting)', 'stunted', 'Tingkatkan asupan protein hewani, sayur, dan buah setiap hari. Pantau tinggi badan rutin di posyandu dan konsultasikan ke tenaga kesehatan.'],
					['gt', 3.0, 'Tinggi', 'tall', 'Pantau pertumbuhan dan pastikan asupan gizi seimbang. Konsultasikan ke tenaga kesehatan jika ada keluhan pertumbuhan.'],
					['default', null, 'Normal', 'normal', 'Pertahankan pola makan seimbang, ASI/MPASI sesuai usia, dan pemantauan rutin di posyandu.'],
				],
			),
			self::zScoreIndicatorRules(
				CalculatorAnjuranRule::INDICATOR_WEIGHT_FOR_AGE,
				[
					['lt', -3.0, 'Berat badan sangat kurang', 'severely_underweight', 'Segera ke Puskesmas untuk penanganan gizi dan pemeriksaan lebih lanjut.'],
					['lt', -2.0, 'Berat badan kurang', 'underweight', 'Tingkatkan frekuensi makan dan MPASI bergizi. Pantau berat badan setiap bulan di posyandu.'],
					['gt', 1.0, 'Risiko berat badan lebih', 'overweight_risk', 'Atur porsi makan seimbang dan aktivitas fisik sesuai usia. Konsultasikan ke tenaga kesehatan.'],
					['default', null, 'Berat badan normal', 'normal', 'Pertahankan asupan gizi seimbang dan pemantauan rutin berat badan.'],
				],
			),
			self::zScoreIndicatorRules(
				CalculatorAnjuranRule::INDICATOR_WEIGHT_FOR_HEIGHT,
				[
					['lt', -3.0, 'Gizi buruk', 'severe_wasting', 'Segera rujuk ke Puskesmas untuk penanganan gizi buruk dan pemantauan medis.'],
					['lt', -2.0, 'Gizi kurang', 'wasting', 'Berikan makanan bergizi tinggi energi dan protein. Pantau berat badan mingguan.'],
					['gt', 3.0, 'Obesitas', 'obese', 'Konsultasikan ke tenaga kesehatan untuk penyesuaian pola makan dan aktivitas fisik.'],
					['gt', 2.0, 'Gizi lebih', 'overweight', 'Kurangi makanan manis/berlemak berlebih dan tingkatkan aktivitas bermain aktif.'],
					['gt', 1.0, 'Berisiko gizi lebih', 'overweight_risk', 'Atur porsi makan dan pantau kenaikan berat badan secara rutin.'],
					['default', null, 'Gizi baik', 'normal', 'Pertahankan pola makan seimbang dan pemantauan rutin di posyandu.'],
				],
			),
			self::zScoreIndicatorRules(
				CalculatorAnjuranRule::INDICATOR_PRIMARY,
				[
					['lt', -2.0, 'Perlu tindak lanjut', 'need_follow_up', 'Hasil skrining menunjukkan risiko gizi. Segera konsultasikan ke posyandu/Puskesmas untuk penanganan dan pemantauan lanjutan.'],
					['default', null, 'Pemantauan rutin', 'normal', 'Hasil skrining dalam batas wajar. Lanjutkan ASI/MPASI bergizi, imunisasi lengkap, dan pemantauan rutin di posyandu.'],
				],
			),
		);
	}

	/**
	 * @param  array<int, array{0: string, 1: float|null, 2: string, 3: string, 4: string}>  $definitions
	 * @return array<int, array<string, mixed>>
	 */
	private static function zScoreIndicatorRules(string $indicator, array $definitions): array
	{
		$rules = [];
		foreach ($definitions as $index => $definition) {
			[$type, $threshold, $label, $slug, $anjuran] = $definition;
			$isDefault = $type === 'default';

			$rules[] = [
				'sort_order' => $index + 1,
				'metric' => CalculatorAnjuranRule::METRIC_Z_SCORE,
				'indicator' => $indicator,
				'threshold' => $threshold,
				'operator' => $isDefault
					? CalculatorAnjuranRule::OPERATOR_GTE
					: $type,
				'is_default' => $isDefault,
				'label' => $label,
				'slug' => $slug,
				'anjuran' => $anjuran,
			];
		}

		return $rules;
	}
}
