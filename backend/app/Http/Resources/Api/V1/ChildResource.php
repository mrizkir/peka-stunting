<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Child */
class ChildResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'gender' => $this->gender,
			'birth_date' => $this->birth_date->format('Y-m-d'),
			'nik' => $this->nik,
			'village' => $this->village,
			'posyandu' => $this->posyandu,
			'notes' => $this->notes,
			'guardian' => GuardianResource::make($this->whenLoaded('guardian')),
			'registered_by' => $this->whenLoaded('registeredBy', fn () => [
				'id' => $this->registeredBy->id,
				'name' => $this->registeredBy->name,
			]),
			'latest_measurement' => MeasurementResource::make($this->whenLoaded('latestMeasurement')),
			'latest_risk_assessment' => RiskAssessmentResource::make($this->whenLoaded('latestRiskAssessment')),
			'measurements' => MeasurementResource::collection($this->whenLoaded('measurements')),
			'created_at' => $this->created_at?->toIso8601String(),
			'updated_at' => $this->updated_at?->toIso8601String(),
		];
	}
}
