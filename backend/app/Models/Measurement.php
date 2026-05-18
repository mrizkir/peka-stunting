<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
	protected $fillable = [
		'child_id',
		'measured_by',
		'measured_at',
		'weight_kg',
		'height_cm',
		'age_months',
		'notes',
	];

	protected function casts(): array
	{
		return [
			'measured_at' => 'date',
			'weight_kg' => 'decimal:2',
			'height_cm' => 'decimal:1',
			'age_months' => 'integer',
		];
	}

	public function child(): BelongsTo
	{
		return $this->belongsTo(Child::class);
	}

	public function measuredBy(): BelongsTo
	{
		return $this->belongsTo(User::class, 'measured_by');
	}

	public function riskAssessment(): \Illuminate\Database\Eloquent\Relations\HasOne
	{
		return $this->hasOne(RiskAssessment::class);
	}
}
