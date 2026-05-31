<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EducationItem extends Model
{
	protected $fillable = [
		'menu_id',
		'parent_id',
		'name',
		'slug',
		'level',
		'sort_order',
	];

	protected function casts(): array
	{
		return [
			'level' => 'integer',
			'sort_order' => 'integer',
		];
	}

	public function menu(): BelongsTo
	{
		return $this->belongsTo(EducationMenu::class, 'menu_id');
	}

	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parent_id');
	}

	public function children(): HasMany
	{
		return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
	}

	public function content(): HasOne
	{
		return $this->hasOne(EducationContent::class, 'item_id');
	}

	public function isRoot(): bool
	{
		return $this->parent_id === null;
	}

	public function isCalculator(): bool
	{
		return in_array($this->slug, [
			'cek-imt',
			'cek-lila',
			'cek-risiko-anemia',
			'cek-keberhasilan-menyusui',
			'periksa-status-gizi',
		], true);
	}

	public function hasScreeningQuestionnaire(): bool
	{
		return $this->slug === 'cek-risiko-anemia';
	}

	public function hasAnjuranRules(): bool
	{
		return $this->anjuranMetric() !== null;
	}

	public function anjuranMetric(): ?string
	{
		return match ($this->slug) {
			'cek-imt' => CalculatorAnjuranRule::METRIC_BMI,
			'cek-lila' => CalculatorAnjuranRule::METRIC_LILA_CM,
			'cek-risiko-anemia' => CalculatorAnjuranRule::METRIC_YES_COUNT,
			'periksa-status-gizi' => CalculatorAnjuranRule::METRIC_Z_SCORE,
			default => null,
		};
	}
}
