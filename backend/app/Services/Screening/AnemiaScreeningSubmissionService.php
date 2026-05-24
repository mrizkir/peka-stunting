<?php

namespace App\Services\Screening;

use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class AnemiaScreeningSubmissionService
{
	public function __construct(
		private readonly AnemiaScreeningEvaluator $evaluator,
	) {}

	/**
	 * @param  array<string, bool>  $answers
	 */
	public function store(
		User $user,
		string $menuSlug,
		array $answers,
	): ScreeningSubmission {
		$item = $this->resolvePublishedAnemiaItem($menuSlug);
		$config = $item->content?->calculator_config;

		if (! is_array($config) || ($config['questions'] ?? []) === []) {
			throw new InvalidArgumentException('Kuesioner anemia belum tersedia di server.');
		}

		$result = $this->evaluator->evaluate($config, $answers);

		return ScreeningSubmission::query()->create([
			'user_id' => $user->id,
			'education_item_id' => $item->id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_CEK_RISIKO_ANEMIA,
			'menu_slug' => $menuSlug,
			'yes_count' => $result['yes_count'],
			'total_questions' => $result['total_questions'],
			'risk_yes_threshold' => $result['risk_yes_threshold'],
			'category' => $result['category'],
			'category_label' => $result['category_label'],
			'answers' => $result['answers'],
			'questions_snapshot' => $config['questions'],
			'submitted_at' => now(),
		]);
	}

	private function resolvePublishedAnemiaItem(string $menuSlug): EducationItem
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->first();
		if ($menu === null) {
			throw new ModelNotFoundException('Menu edukasi tidak ditemukan.');
		}

		$item = $menu->items()
			->where('slug', ScreeningSubmission::CALCULATOR_CEK_RISIKO_ANEMIA)
			->whereHas('content', fn ($query) => $query->published())
			->with('content')
			->first();

		if ($item === null) {
			throw new ModelNotFoundException('Konten Cek Risiko Anemia tidak ditemukan atau belum dipublikasikan.');
		}

		return $item;
	}
}
