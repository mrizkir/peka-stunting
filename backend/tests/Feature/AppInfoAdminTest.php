<?php

namespace Tests\Feature;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\User;
use App\Support\AppInfoContentConfig;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppInfoAdminTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_admin_can_view_app_info_page(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('settings.app-info.edit'))
			->assertOk()
			->assertSee('Info Aplikasi')
			->assertSee('Galeri poster');
	}

	public function test_admin_can_update_app_info_content(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$content = $this->appInfoContent();
		$content->update([
			'status' => EducationContent::STATUS_DRAFT,
			'published_at' => null,
		]);

		$this->actingAs($admin)
			->put(route('settings.app-info.update'), [
				'title' => 'Tentang PEKA Stunting',
				'excerpt' => 'Aplikasi kader untuk edukasi stunting.',
				'body' => '<p>Isi info aplikasi.</p>',
				'status' => EducationContent::STATUS_PUBLISHED,
			])
			->assertRedirect(route('settings.app-info.edit'))
			->assertSessionHas('success');

		$content->refresh();

		$this->assertSame('Tentang PEKA Stunting', $content->title);
		$this->assertSame(EducationContent::STATUS_PUBLISHED, $content->status);
		$this->assertNotNull($content->published_at);
	}

	public function test_info_aplikasi_is_excluded_from_education_menu_index(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('education.index'))
			->assertOk()
			->assertDontSee(AppInfoContentConfig::MENU_SLUG);
	}

	public function test_education_menu_show_redirects_to_app_info_page(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$menu = EducationMenu::query()
			->where('slug', AppInfoContentConfig::MENU_SLUG)
			->firstOrFail();

		$this->actingAs($admin)
			->get(route('education.menus.show', $menu))
			->assertRedirect(route('settings.app-info.edit'));
	}

	private function appInfoContent(): EducationContent
	{
		$menu = EducationMenu::query()
			->where('slug', AppInfoContentConfig::MENU_SLUG)
			->firstOrFail();

		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', AppInfoContentConfig::ITEM_SLUG)
			->firstOrFail();

		return $item->content;
	}
}
