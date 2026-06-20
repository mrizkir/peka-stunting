<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsStatisticsService;
use Illuminate\View\View;

class StatisticsController extends Controller
{
	public function __construct(
		private readonly AnalyticsStatisticsService $statistics,
	) {}

	public function index(): View
	{
		$days = 30;

		return view('statistics.index', [
			'summary' => $this->statistics->summary($days),
			'screeningsByCalculator' => $this->statistics->screeningsByCalculator($days),
			'popularContent' => $this->statistics->popularEducationContent($days),
			'dailyActiveUsers' => $this->statistics->dailyActiveUsers($days),
			'weeklyUsageHours' => $this->statistics->weeklyUsageHours(),
			'topUsersByUsage' => $this->statistics->topUsersByUsage($days),
		]);
	}
}
