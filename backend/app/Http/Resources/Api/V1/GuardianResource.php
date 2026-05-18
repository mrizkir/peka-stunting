<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Guardian */
class GuardianResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'phone' => $this->phone,
			'relationship' => $this->relationship,
			'address' => $this->address,
		];
	}
}
