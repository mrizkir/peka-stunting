<?php

namespace Tests\Feature;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Models\User;
use App\Support\AnemiaScreeningDefaults;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreeningSubmissionAdminTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_admin_can_view_screening_submissions_index(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$submission = $this->createSubmission();

		$response = $this->actingAs($admin)->get(route('screening-submissions.index'));

		$response
			->assertOk()
			->assertSee('Jawaban Cek Risiko Anemia')
			->assertSee($submission->user->name)
			->assertSee($submission->category_label);
	}

	public function test_admin_can_view_screening_submission_detail(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$submission = $this->createSubmission();
		$firstQuestion = AnemiaScreeningDefaults::calculatorConfig()['questions'][0]['text'];

		$response = $this->actingAs($admin)->get(route('screening-submissions.show', $submission));

		$response
			->assertOk()
			->assertSee('Detail Cek Risiko Anemia')
			->assertSee($submission->user->email)
			->assertSee($firstQuestion);
	}

	public function test_non_admin_cannot_access_screening_submissions(): void
	{
		$user = User::factory()->create();
		$user->assignRole('kader');

		$this->actingAs($user)
			->get(route('screening-submissions.index'))
			->assertForbidden();
	}

	public function test_guest_is_redirected_from_screening_submissions(): void
	{
		$this->get(route('screening-submissions.index'))
			->assertRedirect(route('login'));
	}

	private function createSubmission(): ScreeningSubmission
	{
		$config = AnemiaScreeningDefaults::calculatorConfig();
		$user = User::factory()->create();
		$user->assignRole('kader');

		$menu = EducationMenu::query()->where('slug', 'remaja-putri')->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'cek-risiko-anemia')
			->firstOrFail();

		$content = $item->content;
		$content->update([
			'status' => EducationContent::STATUS_PUBLISHED,
			'published_at' => now(),
			'calculator_config' => $config,
		]);

		$answers = [];
		foreach ($config['questions'] as $question) {
			$answers[$question['id']] = $question['id'] === 'fatigue_5l';
		}

		return ScreeningSubmission::query()->create([
			'user_id' => $user->id,
			'education_item_id' => $item->id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_CEK_RISIKO_ANEMIA,
			'menu_slug' => 'remaja-putri',
			'yes_count' => 1,
			'total_questions' => count($config['questions']),
			'risk_yes_threshold' => 3,
			'category' => ScreeningSubmission::CATEGORY_LOW_RISK,
			'category_label' => 'Risiko anemia relatif rendah',
			'answers' => $answers,
			'questions_snapshot' => $config['questions'],
			'submitted_at' => now(),
		]);
	}
}
