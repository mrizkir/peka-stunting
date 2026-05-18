<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guardian extends Model
{
	protected $fillable = [
		'name',
		'phone',
		'relationship',
		'address',
	];

	public function children(): HasMany
	{
		return $this->hasMany(Child::class);
	}
}
