<?php

namespace Tests\Feature\Api;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Models\User;
use App\Support\BreastfeedingSuccessDefaults;
use App\Support\CalculatorAnjuranDefaults;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BreastfeedingScreeningSubmissionApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_authenticated_user_can_store_breastfeeding_submission(): void
	{
		$this->publishMenyusuiContent('ibu-nifas-dan-menyusui');

		$user = User::factory()->create();
		$user->assignRole('kader');
		Sanctum::actingAs($user);

		$config = BreastfeedingSuccessDefaults::calculatorConfig();
		$answers = array_fill_keys(
			array_column($config['questions'], 'id'),
			true,
		);

		$response = $this->postJson('/api/v1/screening-submissions/cek-keberhasilan-menyusui', [
			'menu_slug' => 'ibu-nifas-dan-menyusui',
			'answers' => $answers,
		]);

		$response
			->assertCreated()
			->assertJsonPath('success', true)
			->assertJsonPath('data.calculator_slug', 'cek-keberhasilan-menyusui')
			->assertJsonPath('data.menu_slug', 'ibu-nifas-dan-menyusui')
			->assertJsonPath('data.yes_count', 10)
			->assertJsonPath('data.category', ScreeningSubmission::CATEGORY_NORMAL)
			->assertJsonPath('data.anjuran', fn ($value) => is_string($value) && $value !== '');

		$this->assertDatabaseHas('screening_submissions', [
			'user_id' => $user->id,
			'calculator_slug' => 'cek-keberhasilan-menyusui',
			'menu_slug' => 'ibu-nifas-dan-menyusui',
		]);
	}

	public function test_guest_cannot_store_submission(): void
	{
		$this->publishMenyusuiContent('ibu-nifas-dan-menyusui');

		$this->postJson('/api/v1/screening-submissions/cek-keberhasilan-menyusui', [
			'menu_slug' => 'ibu-nifas-dan-menyusui',
			'answers' => ['good_latch' => true],
		])->assertUnauthorized();
	}

	public function test_user_can_list_own_submissions(): void
	{
		$this->publishMenyusuiContent('ibu-nifas-dan-menyusui');

		$user = User::factory()->create();
		$user->assignRole('kader');
		Sanctum::actingAs($user);

		$config = BreastfeedingSuccessDefaults::calculatorConfig();
		$answers = array_fill_keys(
			array_column($config['questions'], 'id'),
			false,
		);
		$answers['feeding_frequency'] = true;
		$answers['position_latch'] = true;

		$this->postJson('/api/v1/screening-submissions/cek-keberhasilan-menyusui', [
			'menu_slug' => 'ibu-nifas-dan-menyusui',
			'answers' => $answers,
		])->assertCreated();

		$response = $this->getJson('/api/v1/screening-submissions/cek-keberhasilan-menyusui');

		$response
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonCount(1, 'data.items')
			->assertJsonPath('data.items.0.category', 'need_follow_up');
	}

	private function publishMenyusuiContent(string $menuSlug): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'cek-keberhasilan-menyusui')
			->firstOrFail();

		$content = $item->content;
		$content->update([
			'status' => EducationContent::STATUS_PUBLISHED,
			'published_at' => now(),
			'calculator_config' => BreastfeedingSuccessDefaults::calculatorConfig(),
		]);

		$content->anjuranRules()->delete();
		foreach (CalculatorAnjuranDefaults::menyusuiRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh();
	}
}
