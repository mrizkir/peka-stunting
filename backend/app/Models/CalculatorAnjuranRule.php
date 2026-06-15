<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculatorAnjuranRule extends Model
{
	public const METRIC_BMI = 'bmi';

	public const METRIC_LILA_CM = 'lila_cm';

	public const METRIC_YES_COUNT = 'yes_count';

	public const METRIC_Z_SCORE = 'z_score';

	public const INDICATOR_HEIGHT_FOR_AGE = 'height_for_age';

	public const INDICATOR_WEIGHT_FOR_AGE = 'weight_for_age';

	public const INDICATOR_WEIGHT_FOR_HEIGHT = 'weight_for_height';

	public const INDICATOR_PRIMARY = 'primary';

	public const INDICATOR_AGE_10_14 = 'age_10_14';

	public const INDICATOR_AGE_15_17 = 'age_15_17';

	public const INDICATOR_AGE_GT_17 = 'age_gt_17';

	public const OPERATOR_GT = 'gt';

	public const OPERATOR_GTE = 'gte';

	public const OPERATOR_LT = 'lt';

	public const OPERATOR_LTE = 'lte';

	protected $fillable = [
		'education_content_id',
		'sort_order',
		'metric',
		'indicator',
		'threshold',
		'operator',
		'is_default',
		'label',
		'slug',
		'anjuran',
	];

	protected function casts(): array
	{
		return [
			'sort_order' => 'integer',
			'threshold' => 'float',
			'is_default' => 'boolean',
		];
	}

	public function educationContent(): BelongsTo
	{
		return $this->belongsTo(EducationContent::class, 'education_content_id');
	}

	/**
	 * @return array<string, mixed>
	 */
	public function toApiArray(): array
	{
		return [
			'sort_order' => $this->sort_order,
			'metric' => $this->metric,
			'indicator' => $this->indicator,
			'threshold' => $this->threshold,
			'operator' => $this->operator,
			'is_default' => $this->is_default,
			'label' => $this->label,
			'slug' => $this->slug,
			'anjuran' => $this->anjuran,
		];
	}
}
