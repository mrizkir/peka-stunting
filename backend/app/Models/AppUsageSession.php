<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppUsageSession extends Model
{
	protected $fillable = [
		'user_id',
		'session_id',
		'started_at',
		'ended_at',
		'duration_seconds',
		'platform',
		'app_version',
	];

	protected function casts(): array
	{
		return [
			'started_at' => 'datetime',
			'ended_at' => 'datetime',
			'duration_seconds' => 'integer',
		];
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
