<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskAssessment extends Model
{
	public const STATUS_NORMAL = 'normal';

	public const STATUS_RISK = 'risk';

	public const STATUS_NEED_FOLLOW_UP = 'need_follow_up';

	protected $fillable = [
		'child_id',
		'measurement_id',
		'assessed_by',
		'status',
		'score',
		'indicators',
		'summary',
		'assessed_at',
	];

	protected function casts(): array
	{
		return [
			'indicators' => 'array',
			'assessed_at' => 'datetime',
			'score' => 'integer',
		];
	}

	public function child(): BelongsTo
	{
		return $this->belongsTo(Child::class);
	}

	public function measurement(): BelongsTo
	{
		return $this->belongsTo(Measurement::class);
	}

	public function assessedBy(): BelongsTo
	{
		return $this->belongsTo(User::class, 'assessed_by');
	}

	public function statusLabel(): string
	{
		return config("risk_assessment.status_labels.{$this->status}", $this->status);
	}
}
