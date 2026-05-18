<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Measurement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Measurement */
class MeasurementResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'measured_at' => $this->measured_at->format('Y-m-d'),
			'weight_kg' => (float) $this->weight_kg,
			'height_cm' => (float) $this->height_cm,
			'age_months' => $this->age_months,
			'notes' => $this->notes,
			'measured_by' => $this->whenLoaded('measuredBy', fn () => [
				'id' => $this->measuredBy->id,
				'name' => $this->measuredBy->name,
			]),
			'created_at' => $this->created_at?->toIso8601String(),
		];
	}
}
