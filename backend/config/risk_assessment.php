<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Ambang referensi sederhana (MVP)
	|--------------------------------------------------------------------------
	| Nilai minimum berat (kg) dan tinggi (cm) per usia (bulan).
	| Dapat disesuaikan setelah validasi dengan tenaga kesehatan.
	*/
	'reference_by_age_months' => [
		6 => ['weight_kg' => 6.0, 'height_cm' => 63.0],
		12 => ['weight_kg' => 7.8, 'height_cm' => 71.0],
		18 => ['weight_kg' => 8.8, 'height_cm' => 76.5],
		24 => ['weight_kg' => 9.8, 'height_cm' => 82.0],
		36 => ['weight_kg' => 11.8, 'height_cm' => 90.0],
		48 => ['weight_kg' => 13.2, 'height_cm' => 96.0],
		60 => ['weight_kg' => 14.8, 'height_cm' => 102.0],
	],

	'weight_risk_ratio' => 0.90,
	'weight_critical_ratio' => 0.80,
	'height_risk_ratio' => 0.92,
	'height_critical_ratio' => 0.85,

	'status_labels' => [
		'normal' => 'Normal',
		'risk' => 'Risiko',
		'need_follow_up' => 'Perlu tindak lanjut',
	],
];
