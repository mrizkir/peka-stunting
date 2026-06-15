<?php

namespace App\Http\Controllers;

use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScreeningSubmissionController extends Controller
{
	public function index(Request $request): View
	{
		$perPage = (int) $request->integer('per_page', 20);
		$perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 20;
		$search = trim((string) $request->string('q', ''));
		$menuSlug = trim((string) $request->string('menu_slug', ''));
		$calculatorSlug = trim((string) $request->string('calculator_slug', ''));

		$submissions = ScreeningSubmission::query()
			->with('user')
			->when($calculatorSlug !== '', fn ($query) => $query->where('calculator_slug', $calculatorSlug))
			->when($menuSlug !== '', fn ($query) => $query->where('menu_slug', $menuSlug))
			->when($search !== '', function ($query) use ($search) {
				$query->whereHas('user', function ($userQuery) use ($search) {
					$userQuery
						->where('name', 'like', "%{$search}%")
						->orWhere('email', 'like', "%{$search}%")
						->orWhere('phone', 'like', "%{$search}%");
				});
			})
			->orderByDesc('submitted_at')
			->paginate($perPage)
			->withQueryString();

		$menuOptions = EducationMenu::query()
			->orderBy('sort_order')
			->pluck('name', 'slug');

		$calculatorOptions = ScreeningSubmission::calculatorOptions();

		return view('screening-submissions.index', compact(
			'submissions',
			'menuOptions',
			'calculatorOptions',
			'search',
			'menuSlug',
			'calculatorSlug',
			'perPage',
		));
	}

	public function show(ScreeningSubmission $screeningSubmission): View
	{
		$screeningSubmission->load('user', 'educationItem');

		$menuLabel = EducationMenu::query()
			->where('slug', $screeningSubmission->menu_slug)
			->value('name') ?? $screeningSubmission->menu_slug;

		$view = match ($screeningSubmission->calculator_slug) {
			ScreeningSubmission::CALCULATOR_CEK_LILA => 'screening-submissions.show-lila',
			ScreeningSubmission::CALCULATOR_CEK_IMT => 'screening-submissions.show-bmi',
			ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI => 'screening-submissions.show-nutritional-status',
			ScreeningSubmission::CALCULATOR_CEK_KEBERHASILAN_MENYUSUI => 'screening-submissions.show-anemia',
			default => 'screening-submissions.show-anemia',
		};

		return view($view, [
			'submission' => $screeningSubmission,
			'menuLabel' => $menuLabel,
			'answerRows' => $screeningSubmission->answerRows(),
		]);
	}
}
