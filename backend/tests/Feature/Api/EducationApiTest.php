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
			->assertJsonCount(6, 'data');
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
