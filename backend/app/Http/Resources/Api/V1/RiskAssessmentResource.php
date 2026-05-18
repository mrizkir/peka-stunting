<?php

namespace App\Http\Resources\Api\V1;

use App\Models\RiskAssessment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RiskAssessment */
class RiskAssessmentResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'status' => $this->status,
			'status_label' => $this->statusLabel(),
			'score' => $this->score,
			'summary' => $this->summary,
			'indicators' => $this->indicators,
			'assessed_at' => $this->assessed_at->toIso8601String(),
			'measurement' => MeasurementResource::make($this->whenLoaded('measurement')),
			'assessed_by' => $this->whenLoaded('assessedBy', fn () => [
				'id' => $this->assessedBy->id,
				'name' => $this->assessedBy->name,
			]),
			'created_at' => $this->created_at?->toIso8601String(),
		];
	}
}
