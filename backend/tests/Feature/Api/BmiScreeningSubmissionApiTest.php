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

class BmiScreeningSubmissionApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_authenticated_user_can_store_bmi_submission(): void
	{
		$this->publishBmiContent('remaja-putri');

		$user = User::factory()->create();
		$user->assignRole('kader');
		Sanctum::actingAs($user);

		$response = $this->postJson('/api/v1/screening-submissions/cek-imt', [
			'menu_slug' => 'remaja-putri',
			'weight_kg' => 52,
			'height_cm' => 160,
		]);

		$response
			->assertCreated()
			->assertJsonPath('success', true)
			->assertJsonPath('data.calculator_slug', ScreeningSubmission::CALCULATOR_CEK_IMT)
			->assertJsonPath('data.menu_slug', 'remaja-putri')
			->assertJsonPath('data.category', 'normal')
			->assertJsonPath('data.category_label', 'Normal')
			->assertJsonPath('data.answers.bmi', 20.3)
			->assertJsonPath('data.anjuran', fn ($value) => is_string($value) && $value !== '');

		$this->assertDatabaseHas('screening_submissions', [
			'user_id' => $user->id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_CEK_IMT,
			'menu_slug' => 'remaja-putri',
			'category' => 'normal',
			'category_label' => 'Normal',
		]);
	}

	public function test_guest_cannot_store_bmi_submission(): void
	{
		$this->publishBmiContent('remaja-putri');

		$this->postJson('/api/v1/screening-submissions/cek-imt', [
			'menu_slug' => 'remaja-putri',
			'weight_kg' => 52,
			'height_cm' => 160,
		])->assertUnauthorized();
	}

	private function publishBmiContent(string $menuSlug): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', ScreeningSubmission::CALCULATOR_CEK_IMT)
			->firstOrFail();

		$content = $item->content;
		$content->update([
			'status' => EducationContent::STATUS_PUBLISHED,
			'published_at' => now(),
		]);

		$content->anjuranRules()->delete();
		foreach (CalculatorAnjuranDefaults::bmiRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh();
	}
}
