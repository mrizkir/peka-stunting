<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationMenu extends Model
{
	public function getRouteKeyName(): string
	{
		return 'slug';
	}

	protected $fillable = [
		'name',
		'slug',
		'sort_order',
		'description',
	];

	protected function casts(): array
	{
		return [
			'sort_order' => 'integer',
		];
	}

	public function items(): HasMany
	{
		return $this->hasMany(EducationItem::class, 'menu_id');
	}

	public function rootItems(): HasMany
	{
		return $this->items()
			->whereNull('parent_id')
			->orderBy('sort_order');
	}
}
