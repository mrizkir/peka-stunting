<?php

namespace Tests\Feature\Api;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\AnemiaScreeningDefaults;
use Database\Seeders\EducationTaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_menus_index_returns_all_menus(): void
	{
		$response = $this->getJson('/api/v1/education/menus');

		$response
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonCount(7, 'data');
	}

	public function test_menu_show_excludes_unpublished_contents(): void
	{
		$this->publishContent('mengenal-stunting', 'pengertian');

		$response = $this->getJson('/api/v1/education/menus/mengenal-stunting');

		$response
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.slug', 'mengenal-stunting')
			->assertJsonCount(1, 'data.items')
			->assertJsonPath('data.items.0.slug', 'pengertian');
	}

	public function test_menu_show_returns_menu_description(): void
	{
		$response = $this->getJson('/api/v1/education/menus/remaja-putri');

		$response
			->assertOk()
			->assertJsonPath('data.description', fn ($value) => is_string($value)
				&& str_contains($value, 'Hai Remaja Putri, Selamat Datang...')
				&& str_contains($value, 'Pergi menjaring ke Pulau Penyengat,'));
	}

	public function test_content_show_returns_published_content(): void
	{
		$this->publishContent('mengenal-stunting', 'pengertian', [
			'title' => 'Pengertian Stunting',
			'excerpt' => 'Ringkasan singkat',
			'body' => 'Isi lengkap konten.',
			'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
		]);

		$response = $this->getJson('/api/v1/education/menus/mengenal-stunting/contents/pengertian');

		$response
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.title', 'Pengertian Stunting')
			->assertJsonPath('data.excerpt', 'Ringkasan singkat')
			->assertJsonPath('data.body', 'Isi lengkap konten.')
			->assertJsonPath('data.video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
			->assertJsonPath('data.poster_images', [])
			->assertJsonPath('data.status', 'published')
			->assertJsonPath('data.menu.slug', 'mengenal-stunting');
	}

	public function test_content_show_returns_not_found_for_draft(): void
	{
		$response = $this->getJson('/api/v1/education/menus/mengenal-stunting/contents/pengertian');

		$response
			->assertNotFound()
			->assertJsonPath('success', false);
	}

	public function test_calculator_content_returns_excerpt_for_mobile_intro(): void
	{
		$this->publishContent('remaja-putri', 'cek-risiko-anemia', [
			'excerpt' => 'Teks pengantar dari backend.',
		]);

		$response = $this->getJson(
			'/api/v1/education/menus/remaja-putri/contents/cek-risiko-anemia',
		);

		$response
			->assertOk()
			->assertJsonPath('data.excerpt', 'Teks pengantar dari backend.')
			->assertJsonPath('data.type', 'calculator');
	}

	public function test_calculator_content_returns_anjuran_rules_for_cek_imt(): void
	{
		$this->publishContent('remaja-putri', 'cek-imt');
		$content = EducationItem::query()
			->whereHas('menu', fn ($q) => $q->where('slug', 'remaja-putri'))
			->where('slug', 'cek-imt')
			->firstOrFail()
			->content;

		$content->anjuranRules()->delete();
		foreach (\App\Support\CalculatorAnjuranDefaults::bmiRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		$this->getJson('/api/v1/education/menus/remaja-putri/contents/cek-imt')
			->assertOk()
			->assertJsonPath('data.type', 'calculator')
			->assertJsonCount(4, 'data.anjuran_rules')
			->assertJsonPath('data.anjuran_rules.1.label', 'Gemuk');
	}

	public function test_calculator_content_returns_anjuran_rules_for_cek_lila(): void
	{
		$this->publishContent('remaja-putri', 'cek-lila');
		$content = EducationItem::query()
			->whereHas('menu', fn ($q) => $q->where('slug', 'remaja-putri'))
			->where('slug', 'cek-lila')
			->firstOrFail()
			->content;

		$content->anjuranRules()->delete();
		foreach (\App\Support\CalculatorAnjuranDefaults::lilaRulesRemajaPutri() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		$this->getJson('/api/v1/education/menus/remaja-putri/contents/cek-lila')
			->assertOk()
			->assertJsonPath('data.type', 'calculator')
			->assertJsonCount(6, 'data.anjuran_rules')
			->assertJsonPath('data.anjuran_rules.0.label', 'Selamat, status gizi relatif normal')
			->assertJsonPath('data.anjuran_rules.0.metric', 'lila_cm')
			->assertJsonPath('data.anjuran_rules.0.indicator', 'age_10_14');
	}

	public function test_calculator_content_returns_anjuran_rules_for_cek_anemia(): void
	{
		$this->publishContent('remaja-putri', 'cek-risiko-anemia');
		$content = EducationItem::query()
			->whereHas('menu', fn ($q) => $q->where('slug', 'remaja-putri'))
			->where('slug', 'cek-risiko-anemia')
			->firstOrFail()
			->content;

		$content->anjuranRules()->delete();
		foreach (\App\Support\CalculatorAnjuranDefaults::anemiaRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		$this->getJson('/api/v1/education/menus/remaja-putri/contents/cek-risiko-anemia')
			->assertOk()
			->assertJsonCount(4, 'data.anjuran_rules')
			->assertJsonPath('data.anjuran_rules.0.metric', 'yes_count');
	}

	public function test_calculator_content_returns_anjuran_rules_for_status_gizi(): void
	{
		$this->publishContent('bayi-dan-balita', 'periksa-status-gizi');
		$content = EducationItem::query()
			->whereHas('menu', fn ($q) => $q->where('slug', 'bayi-dan-balita'))
			->where('slug', 'periksa-status-gizi')
			->firstOrFail()
			->content;

		$content->anjuranRules()->delete();
		foreach (\App\Support\CalculatorAnjuranDefaults::nutritionalStatusRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		$this->getJson('/api/v1/education/menus/bayi-dan-balita/contents/periksa-status-gizi')
			->assertOk()
			->assertJsonPath('data.anjuran_rules.0.metric', 'z_score')
			->assertJsonPath('data.anjuran_rules.0.indicator', 'height_for_age');
	}

	public function test_calculator_content_returns_questionnaire_config(): void
	{
		$config = AnemiaScreeningDefaults::calculatorConfig();

		$this->publishContent('remaja-putri', 'cek-risiko-anemia', [
			'calculator_config' => $config,
		]);

		$response = $this->getJson(
			'/api/v1/education/menus/remaja-putri/contents/cek-risiko-anemia',
		);

		$response
			->assertOk()
			->assertJsonPath('data.type', 'calculator')
			->assertJsonPath('data.calculator_config.risk_yes_threshold', 3)
			->assertJsonCount(14, 'data.calculator_config.questions')
			->assertJsonPath('data.calculator_config.questions.0.id', 'fatigue_5l');
	}

	public function test_menu_show_includes_kearifan_group_with_published_recipes(): void
	{
		$this->publishContent('remaja-putri', 'nugget-ikan-kembung');

		$response = $this->getJson('/api/v1/education/menus/remaja-putri');

		$response->assertOk();

		$sectionItems = collect($response->json('data.sections'))
			->firstWhere('slug', 'upaya-pencegahan-stunting')['items'] ?? [];

		$group = collect($sectionItems)->firstWhere('slug', 'menu-makanan-kearifan-lokal');

		$this->assertNotNull($group);
		$this->assertSame('group', $group['type']);
		$this->assertCount(1, $group['items']);
		$this->assertSame('nugget-ikan-kembung', $group['items'][0]['slug']);
	}

	public function test_menu_show_hides_kearifan_group_when_no_recipes_published(): void
	{
		$response = $this->getJson('/api/v1/education/menus/remaja-putri');

		$response->assertOk();

		$sectionItems = collect($response->json('data.sections'))
			->firstWhere('slug', 'upaya-pencegahan-stunting')['items'] ?? [];

		$this->assertNull(
			collect($sectionItems)->firstWhere('slug', 'menu-makanan-kearifan-lokal'),
		);
	}

	public function test_menu_show_includes_edukasi_lainnya_when_published(): void
	{
		$this->publishContent('remaja-putri', 'edukasi-lain-nya', [
			'title' => 'Edukasi Lain-nya',
			'excerpt' => 'Materi edukasi tambahan.',
		]);

		$response = $this->getJson('/api/v1/education/menus/remaja-putri');

		$response->assertOk();

		$sectionItems = collect($response->json('data.sections'))
			->firstWhere('slug', 'upaya-pencegahan-stunting')['items'] ?? [];

		$item = collect($sectionItems)->firstWhere('slug', 'edukasi-lain-nya');

		$this->assertNotNull($item);
		$this->assertSame('content', $item['type']);
		$this->assertSame('Edukasi Lain-nya', $item['name']);
	}

	public function test_edukasi_lainnya_seeded_for_all_kebutuhan_mu_menus(): void
	{
		foreach ([
			'remaja-putri',
			'calon-pengantin',
			'ibu-hamil',
			'ibu-nifas-dan-menyusui',
			'bayi-dan-balita',
		] as $menuSlug) {
			$menuId = EducationMenu::query()->where('slug', $menuSlug)->value('id');

			$this->assertNotNull($menuId);
			$this->assertDatabaseHas('education_items', [
				'menu_id' => $menuId,
				'slug' => 'edukasi-lain-nya',
				'name' => 'Edukasi Lain-nya',
			]);
		}
	}

	public function test_bayi_balita_kearifan_group_has_four_recipes(): void
	{
		foreach ([
			'nugget-ikan-kembung',
			'bubur-ikan-kembung',
			'otak-otak-bilis-basah',
			'tim-pindang-ikan-patin-sayuran',
		] as $recipeSlug) {
			$this->publishContent('bayi-dan-balita', $recipeSlug);
		}

		$bayiResponse = $this->getJson('/api/v1/education/menus/bayi-dan-balita');
		$bayiResponse->assertOk();

		$sectionItems = collect($bayiResponse->json('data.sections'))
			->firstWhere('slug', 'upaya-pencegahan-stunting')['items'] ?? [];

		$group = collect($sectionItems)
			->firstWhere('slug', 'menu-makanan-tambahan-berbasis-kearifan-lokal');

		$this->assertNotNull($group);
		$this->assertSame('group', $group['type']);
		$this->assertCount(4, $group['items']);

		$remajaResponse = $this->getJson('/api/v1/education/menus/remaja-putri');
		$remajaResponse->assertOk();

		$remajaSectionItems = collect($remajaResponse->json('data.sections'))
			->firstWhere('slug', 'upaya-pencegahan-stunting')['items'] ?? [];

		$remajaGroup = collect($remajaSectionItems)
			->firstWhere('slug', 'menu-makanan-kearifan-lokal');

		$this->assertNull($remajaGroup);
	}

	/**
	 * @param  array<string, mixed>  $overrides
	 */
	private function publishContent(string $menuSlug, string $itemSlug, array $overrides = []): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', $itemSlug)
			->firstOrFail();

		$content = $item->content;
		$content->update(array_merge([
			'status' => EducationContent::STATUS_PUBLISHED,
			'published_at' => now(),
		], $overrides));

		return $content->fresh();
	}
}
