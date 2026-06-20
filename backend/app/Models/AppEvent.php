<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppEvent extends Model
{
	protected $fillable = [
		'user_id',
		'session_id',
		'event_name',
		'properties',
		'platform',
		'app_version',
		'occurred_at',
	];

	protected function casts(): array
	{
		return [
			'properties' => 'array',
			'occurred_at' => 'datetime',
		];
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
