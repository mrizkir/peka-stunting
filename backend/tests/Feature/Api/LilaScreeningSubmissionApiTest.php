<?php

namespace Tests\Feature\Api;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Models\User;
use App\Support\CalculatorAnjuranDefaults;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LilaScreeningSubmissionApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_authenticated_user_can_store_lila_submission(): void
	{
		$this->publishLilaContent('remaja-putri');

		$user = User::factory()->create();
		$user->assignRole('kader');
		Sanctum::actingAs($user);

		$response = $this->postJson('/api/v1/screening-submissions/cek-lila', [
			'menu_slug' => 'remaja-putri',
			'age_years' => 16,
			'lila_cm' => 21.0,
		]);

		$response
			->assertCreated()
			->assertJsonPath('success', true)
			->assertJsonPath('data.calculator_slug', ScreeningSubmission::CALCULATOR_CEK_LILA)
			->assertJsonPath('data.menu_slug', 'remaja-putri')
			->assertJsonPath('data.category', ScreeningSubmission::CATEGORY_AT_RISK)
			->assertJsonPath('data.category_label', 'Anda berisiko kekurangan gizi (KEK)')
			->assertJsonPath('data.answers.age_years', 16)
			->assertJsonPath('data.answers.lila_cm', 21)
			->assertJsonPath('data.anjuran', fn ($value) => is_string($value) && $value !== '');

		$this->assertDatabaseHas('screening_submissions', [
			'user_id' => $user->id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_CEK_LILA,
			'menu_slug' => 'remaja-putri',
			'category' => ScreeningSubmission::CATEGORY_AT_RISK,
			'category_label' => 'Anda berisiko kekurangan gizi (KEK)',
		]);
	}

	public function test_lila_at_normal_threshold_for_age_15_to_17(): void
	{
		$this->publishLilaContent('remaja-putri');

		$user = User::factory()->create();
		$user->assignRole('kader');
		Sanctum::actingAs($user);

		$this->postJson('/api/v1/screening-submissions/cek-lila', [
			'menu_slug' => 'remaja-putri',
			'age_years' => 17,
			'lila_cm' => 22,
		])
			->assertCreated()
			->assertJsonPath('data.category', ScreeningSubmission::CATEGORY_NORMAL)
			->assertJsonPath('data.category_label', 'Selamat, status gizi relatif normal');
	}

	public function test_guest_cannot_store_lila_submission(): void
	{
		$this->publishLilaContent('remaja-putri');

		$this->postJson('/api/v1/screening-submissions/cek-lila', [
			'menu_slug' => 'remaja-putri',
			'age_years' => 16,
			'lila_cm' => 24,
		])->assertUnauthorized();
	}

	private function publishLilaContent(string $menuSlug): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', ScreeningSubmission::CALCULATOR_CEK_LILA)
			->firstOrFail();

		$content = $item->content;
		$content->update([
			'status' => EducationContent::STATUS_PUBLISHED,
			'published_at' => now(),
		]);

		$content->anjuranRules()->delete();
		$rules = $menuSlug === 'remaja-putri'
			? CalculatorAnjuranDefaults::lilaRulesRemajaPutri()
			: CalculatorAnjuranDefaults::lilaRules();

		foreach ($rules as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh();
	}
}
