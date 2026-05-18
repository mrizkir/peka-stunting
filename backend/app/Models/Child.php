<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Child extends Model
{
	protected $fillable = [
		'guardian_id',
		'registered_by',
		'name',
		'gender',
		'birth_date',
		'nik',
		'village',
		'posyandu',
		'notes',
	];

	protected function casts(): array
	{
		return [
			'birth_date' => 'date',
		];
	}

	public function guardian(): BelongsTo
	{
		return $this->belongsTo(Guardian::class);
	}

	public function registeredBy(): BelongsTo
	{
		return $this->belongsTo(User::class, 'registered_by');
	}

	public function measurements(): HasMany
	{
		return $this->hasMany(Measurement::class)->orderByDesc('measured_at');
	}

	public function latestMeasurement(): HasOne
	{
		return $this->hasOne(Measurement::class)->latestOfMany('measured_at');
	}

	public function riskAssessments(): HasMany
	{
		return $this->hasMany(RiskAssessment::class)->orderByDesc('assessed_at');
	}

	public function latestRiskAssessment(): HasOne
	{
		return $this->hasOne(RiskAssessment::class)->latestOfMany('assessed_at');
	}
}
