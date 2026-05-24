<?php

namespace Database\Seeders;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\AnemiaScreeningDefaults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EducationTaxonomySeeder extends Seeder
{
	public function run(): void
	{
		$menus = $this->taxonomy();

		foreach ($menus as $menuIndex => $menuData) {
			$menu = EducationMenu::updateOrCreate(
				['slug' => $menuData['slug']],
				[
					'name' => $menuData['name'],
					'sort_order' => $menuIndex + 1,
				],
			);

			$this->seedItems($menu, $menuData['items'], null, 1);
		}
	}

	/**
	 * @param  array<int, array{name: string, slug?: string, children?: array}>  $items
	 */
	private function seedItems(EducationMenu $menu, array $items, ?int $parentId, int $level): void
	{
		foreach ($items as $index => $itemData) {
			$name = $itemData['name'];
			$slug = $itemData['slug'] ?? Str::slug($name);
			$children = $itemData['children'] ?? [];

			$item = EducationItem::updateOrCreate(
				[
					'menu_id' => $menu->id,
					'slug' => $slug,
					'parent_id' => $parentId,
				],
				[
					'name' => $name,
					'level' => $level,
					'sort_order' => $index + 1,
				],
			);

			if ($children !== []) {
				$this->seedItems($menu, $children, $item->id, $level + 1);
			} else {
				$attributes = [
					'title' => $name,
					'status' => EducationContent::STATUS_DRAFT,
				];

				if ($slug === 'cek-risiko-anemia') {
					$attributes['excerpt'] = 'Anemia pada remaja adalah kondisi ketika kadar hemoglobin (Hb) dalam darah rendah, sehingga tubuh kekurangan oksigen. Ini sering terjadi karena kekurangan zat besi, asupan gizi yang kurang, atau pola makan tidak seimbang. Distribusi oksigen ke jaringan terganggu mengakibatkan pertumbuhan tulang dan otot jadi tidak optimal sehingga resiko stunting meningkat.';
					$attributes['calculator_config'] = AnemiaScreeningDefaults::calculatorConfig();
				}

				$content = EducationContent::firstOrCreate(
					['item_id' => $item->id],
					$attributes,
				);

				if ($slug === 'cek-risiko-anemia') {
					$updates = [];
					if (blank($content->excerpt)) {
						$updates['excerpt'] = $attributes['excerpt'];
					}
					if (blank($content->calculator_config)) {
						$updates['calculator_config'] = $attributes['calculator_config'];
					}
					if ($updates !== []) {
						$content->update($updates);
					}
				}
			}
		}
	}

	/**
	 * @return array<int, array{slug: string, name: string, items: array}>
	 */
	private function taxonomy(): array
	{
		return [
			[
				'slug' => 'mengenal-stunting',
				'name' => 'Mengenal Stunting',
				'items' => [
					['name' => 'Pengertian'],
					['name' => 'Ciri-Ciri', 'slug' => 'ciri-ciri'],
					['name' => 'Penyebab'],
					['name' => 'Siapa yang Berisiko', 'slug' => 'siapa-yang-berisiko'],
					['name' => 'Dampak'],
				],
			],
			[
				'slug' => 'remaja-putri',
				'name' => 'Remaja Putri',
				'items' => array_merge($this->deteksiDiniItems(), [
					[
						'name' => 'Upaya Pencegahan Stunting',
						'slug' => 'upaya-pencegahan-stunting',
						'children' => [
							['name' => 'Pola Gizi Seimbang'],
							['name' => 'Cara Cegah Anemia'],
							['name' => 'Olahraga Rutin'],
							['name' => 'Hindari Rokok'],
							['name' => 'Bahaya Begadang'],
							['name' => 'Jaga Organ Kesehatan Reproduksi', 'slug' => 'jaga-organ-kesehatan-reproduksi'],
							['name' => 'Kebersihan Diri dan Lingkungan', 'slug' => 'kebersihan-diri-dan-lingkungan'],
						],
					],
				]),
			],
			[
				'slug' => 'calon-pengantin',
				'name' => 'Calon Pengantin',
				'items' => array_merge($this->deteksiDiniItems(), [
					[
						'name' => 'Upaya Pencegahan Stunting',
						'slug' => 'upaya-pencegahan-stunting',
						'children' => [
							['name' => 'Cegah Anemia'],
							['name' => 'Jaga Alat Reproduksi'],
							['name' => 'Gaya Hidup Sehat'],
							['name' => 'Rencanakan kehamilan dengan baik', 'slug' => 'rencanakan-kehamilan-dengan-baik'],
							['name' => 'Persiapkan 1000 hari pertama kehidupan', 'slug' => 'persiapkan-1000-hari-pertama-kehidupan'],
						],
					],
				]),
			],
			[
				'slug' => 'ibu-hamil',
				'name' => 'Ibu Hamil',
				'items' => array_merge($this->deteksiDiniItems(), [
					[
						'name' => 'Upaya Pencegahan Stunting',
						'slug' => 'upaya-pencegahan-stunting',
						'children' => [
							['name' => 'Penuhi Kebutuhan Nutrisi'],
							['name' => 'Menu Camilan Kearifan Lokal', 'slug' => 'menu-camilan-kearifan-lokal'],
							['name' => 'Lakukan Pemeriksaan Kehamilan Secara Rutin', 'slug' => 'lakukan-pemeriksaan-kehamilan-secara-rutin'],
							['name' => 'Terapkan Perilaku Hidup Bersih dan Sehat', 'slug' => 'terapkan-perilaku-hidup-bersih-dan-sehat'],
							['name' => 'Hindari Paparan Asap Rokok', 'slug' => 'hindari-paparan-asap-rokok'],
							['name' => 'Olahraga secara rutin', 'slug' => 'olahraga-secara-rutin'],
						],
					],
				]),
			],
			[
				'slug' => 'ibu-nifas-dan-menyusui',
				'name' => 'Ibu Nifas dan Menyusui',
				'items' => [
					[
						'name' => 'Deteksi Dini',
						'slug' => 'deteksi-dini',
						'children' => [
							['name' => 'Cek Keberhasilan Menyusui', 'slug' => 'cek-keberhasilan-menyusui'],
							['name' => 'Cek LILA', 'slug' => 'cek-lila'],
							['name' => 'Cek Risiko Anemia', 'slug' => 'cek-risiko-anemia'],
						],
					],
					[
						'name' => 'Upaya Pencegahan Stunting',
						'slug' => 'upaya-pencegahan-stunting',
						'children' => [
							['name' => 'Terapkan ASI Eksklusif'],
							['name' => 'Teknik Meningkatkan Produksi ASI', 'slug' => 'teknik-meningkatkan-produksi-asi'],
							['name' => 'Cara Menyusui yang Benar', 'slug' => 'cara-menyusui-yang-benar'],
							['name' => 'Penuhi Kebutuhan Gizi Seimbang', 'slug' => 'penuhi-kebutuhan-gizi-seimbang'],
							['name' => 'Makanan Kearifan Lokal Penambah Produksi ASI', 'slug' => 'makanan-kearifan-lokal-penambah-produksi-asi'],
							['name' => 'Berolahraga Secara Rutin', 'slug' => 'berolahraga-secara-rutin'],
							['name' => 'Hindari Stres'],
							['name' => 'Persiapkan KB', 'slug' => 'persiapkan-kb'],
							['name' => 'Cara Menyimpan ASI Perah', 'slug' => 'cara-menyimpan-asi-perah'],
						],
					],
				],
			],
			[
				'slug' => 'bayi-dan-balita',
				'name' => 'Bayi dan Balita',
				'items' => [
					[
						'name' => 'Deteksi Dini',
						'slug' => 'deteksi-dini',
						'children' => [
							['name' => 'Periksa Status Gizi', 'slug' => 'periksa-status-gizi'],
						],
					],
					[
						'name' => 'Upaya Pencegahan Stunting',
						'slug' => 'upaya-pencegahan-stunting',
						'children' => [
							['name' => 'Pemberian ASI'],
							['name' => 'Pemberian Makanan Pendamping ASI yang Benar', 'slug' => 'pemberian-makanan-pendamping-asi-yang-benar'],
							['name' => 'Menu Makanan Tambahan Berbasis Kearifan Lokal', 'slug' => 'menu-makanan-tambahan-berbasis-kearifan-lokal'],
							['name' => 'Rutin Memantau Pertumbuhan Balita', 'slug' => 'rutin-memantau-pertumbuhan-balita'],
							['name' => 'Melakukan Stimulasi Dini Perkembangan Anak', 'slug' => 'melakukan-stimulasi-dini-perkembangan-anak'],
							['name' => 'Imunisasi'],
						],
					],
				],
			],
		];
	}

	/**
	 * @return array<int, array{name: string, slug: string, children: array}>
	 */
	private function deteksiDiniItems(): array
	{
		return [
			[
				'name' => 'Deteksi Dini',
				'slug' => 'deteksi-dini',
				'children' => [
					['name' => 'Cek IMT', 'slug' => 'cek-imt'],
					['name' => 'Cek LILA', 'slug' => 'cek-lila'],
					['name' => 'Cek Risiko Anemia', 'slug' => 'cek-risiko-anemia'],
				],
			],
		];
	}
}
