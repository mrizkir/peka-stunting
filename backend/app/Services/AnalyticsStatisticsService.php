<?php

namespace App\Services;

use App\Models\AppEvent;
use App\Models\AppUsageSession;
use App\Models\Child;
use App\Models\ScreeningSubmission;
use App\Models\User;
use App\Support\AnalyticsEventName;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalyticsStatisticsService
{
	public function summary(int $activeDays = 30): array
	{
		$since = now()->subDays($activeDays)->startOfDay();

		$totalUsers = User::query()->count();
		$activeFromEvents = AppEvent::query()
			->where('occurred_at', '>=', $since)
			->whereNotNull('user_id')
			->distinct('user_id')
			->count('user_id');
		$activeFromScreening = ScreeningSubmission::query()
			->where('submitted_at', '>=', $since)
			->distinct('user_id')
			->count('user_id');
		$activeKaders = max($activeFromEvents, $activeFromScreening);

		$totalScreenings = ScreeningSubmission::query()->count();
		$screeningsInPeriod = ScreeningSubmission::query()
			->where('submitted_at', '>=', $since)
			->count();
		$totalChildren = Child::query()->count();

		$sessionStats = AppUsageSession::query()
			->where('started_at', '>=', $since)
			->selectRaw('COUNT(*) as session_count')
			->selectRaw('COALESCE(SUM(duration_seconds), 0) as total_seconds')
			->selectRaw('COALESCE(AVG(duration_seconds), 0) as avg_seconds')
			->first();

		return [
			'active_days' => $activeDays,
			'total_users' => $totalUsers,
			'active_kaders' => $activeKaders,
			'total_screenings' => $totalScreenings,
			'screenings_in_period' => $screeningsInPeriod,
			'total_children' => $totalChildren,
			'session_count' => (int) ($sessionStats->session_count ?? 0),
			'total_usage_hours' => round(((int) ($sessionStats->total_seconds ?? 0)) / 3600, 1),
			'avg_session_minutes' => round(((float) ($sessionStats->avg_seconds ?? 0)) / 60, 1),
		];
	}

	/**
	 * @return Collection<int, object{calculator_slug: string, total: int}>
	 */
	public function screeningsByCalculator(int $days = 30): Collection
	{
		$since = now()->subDays($days)->startOfDay();

		return ScreeningSubmission::query()
			->select('calculator_slug')
			->selectRaw('COUNT(*) as total')
			->where('submitted_at', '>=', $since)
			->groupBy('calculator_slug')
			->orderByDesc('total')
			->get()
			->map(function ($row) {
				$row->label = ScreeningSubmission::calculatorOptions()[$row->calculator_slug]
					?? $row->calculator_slug;

				return $row;
			});
	}

	/**
	 * @return Collection<int, object{slug: string, total: int}>
	 */
	public function popularEducationContent(int $days = 30, int $limit = 10): Collection
	{
		$since = now()->subDays($days)->startOfDay();

		return AppEvent::query()
			->where('event_name', AnalyticsEventName::EDUCATION_CONTENT_VIEW)
			->where('occurred_at', '>=', $since)
			->get(['properties'])
			->map(function (AppEvent $event) {
				$menuSlug = $event->properties['menu_slug'] ?? '';
				$itemSlug = $event->properties['item_slug'] ?? '';

				return trim("{$menuSlug}/{$itemSlug}", '/');
			})
			->filter(fn (string $slug) => $slug !== '')
			->countBy()
			->sortDesc()
			->take($limit)
			->map(fn (int $total, string $slug) => (object) [
				'slug' => $slug,
				'total' => $total,
			])
			->values();
	}

	/**
	 * @return list<array{date: string, total: int}>
	 */
	public function dailyActiveUsers(int $days = 30): array
	{
		$since = now()->subDays($days - 1)->startOfDay();

		$fromEvents = AppEvent::query()
			->where('occurred_at', '>=', $since)
			->whereNotNull('user_id')
			->selectRaw('DATE(occurred_at) as day')
			->selectRaw('COUNT(DISTINCT user_id) as total')
			->groupBy('day')
			->pluck('total', 'day');

		$fromScreening = ScreeningSubmission::query()
			->where('submitted_at', '>=', $since)
			->selectRaw('DATE(submitted_at) as day')
			->selectRaw('COUNT(DISTINCT user_id) as total')
			->groupBy('day')
			->pluck('total', 'day');

		$rows = [];

		for ($i = 0; $i < $days; $i++) {
			$day = Carbon::now()->subDays($days - 1 - $i)->toDateString();
			$rows[] = [
				'date' => $day,
				'total' => max((int) ($fromEvents[$day] ?? 0), (int) ($fromScreening[$day] ?? 0)),
			];
		}

		return $rows;
	}

	/**
	 * @return list<array{week: string, total_hours: float}>
	 */
	public function weeklyUsageHours(int $weeks = 8): array
	{
		$since = now()->subWeeks($weeks)->startOfWeek();

		$grouped = AppUsageSession::query()
			->where('started_at', '>=', $since)
			->get(['started_at', 'duration_seconds'])
			->groupBy(fn (AppUsageSession $session) => $session->started_at->copy()->startOfWeek()->format('Y-m-d'))
			->map(fn (Collection $items) => (int) $items->sum('duration_seconds'));

		$rows = [];

		for ($i = 0; $i < $weeks; $i++) {
			$weekStart = Carbon::now()->subWeeks($weeks - 1 - $i)->startOfWeek();
			$key = $weekStart->format('Y-m-d');
			$rows[] = [
				'week' => $weekStart->format('d M Y'),
				'total_hours' => round(((int) ($grouped[$key] ?? 0)) / 3600, 1),
			];
		}

		return $rows;
	}

	/**
	 * @return Collection<int, object{user_id: int, name: string, total_seconds: int, session_count: int}>
	 */
	public function topUsersByUsage(int $days = 30, int $limit = 10): Collection
	{
		$since = now()->subDays($days)->startOfDay();

		return AppUsageSession::query()
			->where('started_at', '>=', $since)
			->whereNotNull('user_id')
			->select('user_id')
			->selectRaw('SUM(duration_seconds) as total_seconds')
			->selectRaw('COUNT(*) as session_count')
			->groupBy('user_id')
			->orderByDesc('total_seconds')
			->limit($limit)
			->get()
			->map(function ($row) {
				$user = User::query()->find($row->user_id);
				$row->name = $user?->name ?? 'User #'.$row->user_id;

				return $row;
			});
	}
}
