<?php

namespace App\Support;

class AnemiaScreeningDefaults
{
	/**
	 * Kuesioner skrining risiko anemia (14 pertanyaan).
	 *
	 * @return array<string, mixed>
	 */
	public static function calculatorConfig(): array
	{
		return [
			'risk_yes_threshold' => 3,
			'questions' => [
				[
					'id' => 'fatigue_5l',
					'text' => 'Apakah Anda sering merasa lelah, letih, lesu, lemah, lalai (5L)?',
				],
				[
					'id' => 'dizziness_headache',
					'text' => 'Apakah Anda sering merasa pusing, sakit kepala, atau mata berkunang-kunang?',
				],
				[
					'id' => 'pale_skin',
					'text' => 'Apakah kulit, telapak tangan, atau bagian dalam kelopak mata Anda terlihat pucat?',
				],
				[
					'id' => 'shortness_breath',
					'text' => 'Apakah Anda sering merasa napas pendek atau sesak setelah aktivitas ringan?',
				],
				[
					'id' => 'heart_palpitation',
					'text' => 'Apakah Anda sering merasa jantung berdebar-debar?',
				],
				[
					'id' => 'concentration',
					'text' => 'Apakah Anda sering merasa sulit berkonsentrasi?',
				],
				[
					'id' => 'cold_hands_feet',
					'text' => 'Apakah Anda sering merasa kedinginan pada tangan atau kaki?',
				],
				[
					'id' => 'low_iron_food',
					'text' => 'Apakah Anda jarang mengonsumsi sumber zat besi (daging merah, hati, ikan, sayuran hijau) dalam seminggu?',
				],
				[
					'id' => 'tea_coffee_with_meal',
					'text' => 'Apakah Anda sering minum teh atau kopi saat atau segera setelah makan?',
				],
				[
					'id' => 'skip_breakfast',
					'text' => 'Apakah Anda melewatkan sarapan secara rutin?',
				],
				[
					'id' => 'strict_diet',
					'text' => 'Apakah Anda sedang dalam program diet ketat (mengurangi porsi makan secara drastis)?',
				],
				[
					'id' => 'heavy_menstruation',
					'text' => 'Apakah siklus menstruasi Anda teratur, tetapi darah yang keluar sangat banyak atau lama (> 7 hari)?',
				],
				[
					'id' => 'previous_anemia',
					'text' => 'Apakah Anda pernah didiagnosa anemia sebelumnya?',
				],
				[
					'id' => 'low_ttd',
					'text' => 'Apakah Anda jarang/tidak pernah mengonsumsi Tablet Tambah Darah (TTD)?',
				],
			],
		];
	}
}
