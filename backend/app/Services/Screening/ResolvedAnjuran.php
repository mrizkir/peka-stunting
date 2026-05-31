<?php

namespace App\Services\Screening;

readonly class ResolvedAnjuran
{
	public function __construct(
		public string $slug,
		public string $label,
		public string $anjuran,
	) {}

	/**
	 * @return array{category: string, category_label: string, anjuran: string}
	 */
	public function toArray(): array
	{
		return [
			'category' => $this->slug,
			'category_label' => $this->label,
			'anjuran' => $this->anjuran,
		];
	}
}
