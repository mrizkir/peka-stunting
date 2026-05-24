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

		$submissions = ScreeningSubmission::query()
			->with('user')
			->where('calculator_slug', ScreeningSubmission::CALCULATOR_CEK_RISIKO_ANEMIA)
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

		return view('screening-submissions.index', compact(
			'submissions',
			'menuOptions',
			'search',
			'menuSlug',
			'perPage',
		));
	}

	public function show(ScreeningSubmission $screeningSubmission): View
	{
		$screeningSubmission->load('user', 'educationItem');

		$menuLabel = EducationMenu::query()
			->where('slug', $screeningSubmission->menu_slug)
			->value('name') ?? $screeningSubmission->menu_slug;

		return view('screening-submissions.show', [
			'submission' => $screeningSubmission,
			'menuLabel' => $menuLabel,
			'answerRows' => $screeningSubmission->answerRows(),
		]);
	}
}
