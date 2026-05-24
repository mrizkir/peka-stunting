<?php

namespace Tests\Feature\Api;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Models\User;
use App\Support\AnemiaScreeningDefaults;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnemiaScreeningSubmissionApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_authenticated_user_can_store_anemia_screening_submission(): void
	{
		$this->publishAnemiaContent('remaja-putri');

		$user = User::factory()->create();
		$user->assignRole('kader');
		Sanctum::actingAs($user);

		$config = AnemiaScreeningDefaults::calculatorConfig();
		$answers = [];
		foreach ($config['questions'] as $question) {
			$answers[$question['id']] = $question['id'] === 'fatigue_5l';
		}

		$response = $this->postJson('/api/v1/screening-submissions/cek-risiko-anemia', [
			'menu_slug' => 'remaja-putri',
			'answers' => $answers,
		]);

		$response
			->assertCreated()
			->assertJsonPath('success', true)
			->assertJsonPath('data.calculator_slug', 'cek-risiko-anemia')
			->assertJsonPath('data.menu_slug', 'remaja-putri')
			->assertJsonPath('data.yes_count', 1)
			->assertJsonPath('data.category', ScreeningSubmission::CATEGORY_LOW_RISK);

		$this->assertDatabaseHas('screening_submissions', [
			'user_id' => $user->id,
			'calculator_slug' => 'cek-risiko-anemia',
			'menu_slug' => 'remaja-putri',
		]);
	}

	public function test_guest_cannot_store_submission(): void
	{
		$this->publishAnemiaContent('remaja-putri');

		$this->postJson('/api/v1/screening-submissions/cek-risiko-anemia', [
			'menu_slug' => 'remaja-putri',
			'answers' => ['fatigue_5l' => true],
		])->assertUnauthorized();
	}

	public function test_user_can_list_own_submissions(): void
	{
		$this->publishAnemiaContent('remaja-putri');

		$user = User::factory()->create();
		$user->assignRole('kader');
		Sanctum::actingAs($user);

		$config = AnemiaScreeningDefaults::calculatorConfig();
		$answers = array_fill_keys(
			array_column($config['questions'], 'id'),
			false,
		);
		$answers['fatigue_5l'] = true;
		$answers['dizziness_headache'] = true;
		$answers['concentration'] = true;

		$this->postJson('/api/v1/screening-submissions/cek-risiko-anemia', [
			'menu_slug' => 'remaja-putri',
			'answers' => $answers,
		])->assertCreated();

		$response = $this->getJson('/api/v1/screening-submissions/cek-risiko-anemia');

		$response
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonCount(1, 'data.items')
			->assertJsonPath('data.items.0.category', ScreeningSubmission::CATEGORY_AT_RISK);
	}

	private function publishAnemiaContent(string $menuSlug): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'cek-risiko-anemia')
			->firstOrFail();

		$content = $item->content;
		$content->update([
			'status' => EducationContent::STATUS_PUBLISHED,
			'published_at' => now(),
			'calculator_config' => AnemiaScreeningDefaults::calculatorConfig(),
		]);

		return $content->fresh();
	}
}
