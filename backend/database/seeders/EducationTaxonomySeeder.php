<?php

namespace Database\Seeders;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\AnemiaScreeningDefaults;
use App\Support\BreastfeedingSuccessDefaults;
use App\Support\EducationMenuDescriptions;
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
          'description' => $menuData['description']
            ?? EducationMenuDescriptions::forSlug($menuData['slug']),
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
        $item->content()->delete();
        $this->seedItems($menu, $children, $item->id, $level + 1);
      } else {
        $attributes = [
          'title' => $name,
          'status' => EducationContent::STATUS_DRAFT,
        ];

        if ($slug === 'info-aplikasi') {
          $attributes['title'] = 'Info Aplikasi';
          $attributes['excerpt'] = 'Aplikasi kader untuk edukasi stunting.';
        }

        if ($slug === 'cek-risiko-anemia') {
          $attributes['excerpt'] = 'Anemia pada remaja adalah kondisi ketika kadar hemoglobin (Hb) dalam darah rendah, sehingga tubuh kekurangan oksigen. Ini sering terjadi karena kekurangan zat besi, asupan gizi yang kurang, atau pola makan tidak seimbang. Distribusi oksigen ke jaringan terganggu mengakibatkan pertumbuhan tulang dan otot jadi tidak optimal sehingga resiko stunting meningkat.';
          $attributes['calculator_config'] = AnemiaScreeningDefaults::calculatorConfig();
        }

        if ($slug === 'cek-keberhasilan-menyusui') {
          $attributes['excerpt'] = 'Cek keberhasilan menyusui membantu ibu menilai apakah ASI eksklusif berjalan dengan baik. Jawab pertanyaan berikut berdasarkan kondisi bayi dan pola menyusui Anda.';
          $attributes['calculator_config'] = BreastfeedingSuccessDefaults::calculatorConfig();
        }

        $content = EducationContent::firstOrCreate(
          ['item_id' => $item->id],
          $attributes,
        );

        if ($slug === 'info-aplikasi') {
          $updates = [];
          if (blank($content->excerpt)) {
            $updates['excerpt'] = $attributes['excerpt'];
          }
          if ($updates !== []) {
            $content->update($updates);
          }
        }

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

        if ($slug === 'cek-keberhasilan-menyusui') {
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
        'slug' => 'info-aplikasi',
        'name' => 'Info Aplikasi',
        'description' => 'Tentang aplikasi PEKA Stunting.',
        'items' => [
          ['name' => 'Info Aplikasi', 'slug' => 'info-aplikasi'],
        ],
      ],
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
              ['name' => 'Pola Gizi Seimbang', 'slug' => 'pola-gizi-seimbang'],
              ['name' => 'Cara Cegah Anemia', 'slug' => 'cara-cegah-anemia'],
              $this->menuMakananKearifanLokalUmum(),
              ['name' => 'Olahraga Rutin', 'slug' => 'olahraga-rutin'],
              ['name' => 'Bahaya Begadang', 'slug' => 'bahaya-begadang'],
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
              ['name' => 'Pola Gizi Seimbang', 'slug' => 'pola-gizi-seimbang'],
              ['name' => 'Cegah Anemia', 'slug' => 'cegah-anemia'],
              $this->menuMakananKearifanLokalUmum(),
              ['name' => 'Jaga Alat Reproduksi', 'slug' => 'jaga-alat-reproduksi'],
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
              ['name' => 'Penuhi Kebutuhan Nutrisi', 'slug' => 'penuhi-kebutuhan-nutrisi'],
              $this->menuMakananKearifanLokalUmum(),
              ['name' => 'Lakukan Pemeriksaan Kehamilan Secara Rutin', 'slug' => 'lakukan-pemeriksaan-kehamilan-secara-rutin'],
              ['name' => 'Jaga Kebersihan Diri', 'slug' => 'jaga-kebersihan-diri'],
              ['name' => 'Hindari Paparan Asap Rokok', 'slug' => 'hindari-paparan-asap-rokok'],
              ['name' => 'Olahraga secara rutin', 'slug' => 'olahraga-secara-rutin'],
              ['name' => 'Hindari Stres', 'slug' => 'hindari-stres'],
              ['name' => 'Istirahat yang Cukup', 'slug' => 'istirahat-yang-cukup'],
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
              ['name' => 'Terapkan ASI Eksklusif', 'slug' => 'terapkan-asi-eksklusif'],
              ['name' => 'Teknik Meningkatkan Produksi ASI', 'slug' => 'teknik-meningkatkan-produksi-asi'],
              ['name' => 'Penuhi Kebutuhan Nutrisi', 'slug' => 'penuhi-kebutuhan-gizi-seimbang'],
              $this->menuMakananKearifanLokalUmum('makanan-kearifan-lokal-penambah-produksi-asi'),
              ['name' => 'Persiapkan KB', 'slug' => 'persiapkan-kb'],
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
              ['name' => 'Pemberian ASI', 'slug' => 'pemberian-asi'],
              ['name' => 'Pemberian MPASI yang Benar', 'slug' => 'pemberian-makanan-pendamping-asi-yang-benar'],
              $this->menuMakananKearifanLokalBayiBalita(),
              ['name' => 'Memantau Tumbuh Kembang', 'slug' => 'rutin-memantau-pertumbuhan-balita'],
              ['name' => 'Imunisasi', 'slug' => 'imunisasi'],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * @return array{name: string, slug: string, children: array<int, array{name: string}>}
   */
  private function menuMakananKearifanLokalUmum(string $slug = 'menu-makanan-kearifan-lokal'): array
  {
    return [
      'name' => 'Menu Makanan Kearifan Lokal',
      'slug' => $slug,
      'children' => [
        ['name' => 'Tumis Jantung Pisang Bilis Basah'],
        ['name' => 'Pindang Ikan Amoy'],
        ['name' => 'Nugget Ikan Kembung'],
        ['name' => 'Otak-Otak Bilis Basah'],
        ['name' => 'Tim Pindang Ikan Patin Sayuran'],
        ['name' => 'Dadar Telur Ikan Bilis Daun Singkong'],
        ['name' => 'Dimsum Ikan Kembung Tahu Wortel'],
        ['name' => 'Tumis Daun Pepaya Bilis Basah'],
      ],
    ];
  }

  /**
   * @return array{name: string, slug: string, children: array<int, array{name: string}>}
   */
  private function menuMakananKearifanLokalBayiBalita(): array
  {
    return [
      'name' => 'Menu Makanan Kearifan Lokal',
      'slug' => 'menu-makanan-tambahan-berbasis-kearifan-lokal',
      'children' => [
        ['name' => 'Nugget Ikan Kembung'],
        ['name' => 'Bubur Ikan Kembung'],
        ['name' => 'Otak-Otak Bilis Basah'],
        ['name' => 'Tim Pindang Ikan Patin Sayuran'],
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
