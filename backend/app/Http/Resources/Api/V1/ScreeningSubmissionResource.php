<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ScreeningSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ScreeningSubmission */
class ScreeningSubmissionResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'calculator_slug' => $this->calculator_slug,
			'menu_slug' => $this->menu_slug,
			'yes_count' => $this->yes_count,
			'total_questions' => $this->total_questions,
			'risk_yes_threshold' => $this->risk_yes_threshold,
			'category' => $this->category,
			'category_label' => $this->category_label,
			'answers' => $this->answers,
			'submitted_at' => $this->submitted_at?->toIso8601String(),
		];
	}
}
