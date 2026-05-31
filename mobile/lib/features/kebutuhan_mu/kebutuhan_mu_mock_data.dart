import 'kebutuhan_mu_config.dart';

/// Data mockup navigasi Kebutuhanmu (tanpa backend).
class KebutuhanMuMockData {
  KebutuhanMuMockData._();

  static const deteksiDiniBayiBalitaItems = [
    KebutuhanMuMockItem(
      slug: 'periksa-status-gizi',
      name: 'Periksa Status Gizi',
      subtitle: 'Skrining berat dan tinggi badan balita.',
      isCalculator: true,
    ),
  ];

  static const deteksiDiniItems = [
    KebutuhanMuMockItem(
      slug: 'cek-imt',
      name: 'Cek IMT',
      subtitle: 'Hitung indeks massa tubuh.',
      isCalculator: true,
    ),
    KebutuhanMuMockItem(
      slug: 'cek-lila',
      name: 'Cek LILA',
      subtitle: 'Ukur lingkar lengan atas.',
      isCalculator: true,
    ),
    KebutuhanMuMockItem(
      slug: 'cek-risiko-anemia',
      name: 'Cek Risiko Anemia',
      subtitle: 'Skrining risiko anemia.',
      isCalculator: true,
    ),
  ];

  static const upayaPencegahanItems = [
    KebutuhanMuMockItem(
      slug: 'pola-gizi-seimbang',
      name: 'Pola Gizi Seimbang',
      subtitle: 'Panduan pola gizi seimbang.',
    ),
    KebutuhanMuMockItem(
      slug: 'cara-cegah-anemia',
      name: 'Cara Cegah Anemia',
      subtitle: 'Tips mencegah anemia.',
    ),
    KebutuhanMuMockItem(
      slug: 'olahraga-rutin',
      name: 'Olahraga Rutin',
      subtitle: 'Manfaat olahraga rutin.',
    ),
    KebutuhanMuMockItem(
      slug: 'hindari-rokok',
      name: 'Hindari Rokok',
      subtitle: 'Bahaya rokok dan cara menghindarinya.',
    ),
    KebutuhanMuMockItem(
      slug: 'bahaya-begadang',
      name: 'Bahaya Begadang',
      subtitle: 'Dampak begadang pada kesehatan.',
    ),
    KebutuhanMuMockItem(
      slug: 'jaga-organ-kesehatan-reproduksi',
      name: 'Jaga Organ Kesehatan Reproduksi',
      subtitle: 'Perawatan organ kesehatan reproduksi.',
    ),
    KebutuhanMuMockItem(
      slug: 'kebersihan-diri-dan-lingkungan',
      name: 'Kebersihan Diri dan Lingkungan',
      subtitle: 'PHBS untuk diri dan lingkungan.',
    ),
  ];

  static List<KebutuhanMuMockItem> itemsForSection(String sectionSlug) {
    switch (sectionSlug) {
      case KebutuhanMuConfig.deteksiDiniSlug:
        return deteksiDiniItems;
      case KebutuhanMuConfig.upayaPencegahanSlug:
        return upayaPencegahanItems;
      default:
        return const [];
    }
  }

  static KebutuhanMuMockItem? findItem(String itemSlug) {
    for (final item in [
      ...deteksiDiniItems,
      ...deteksiDiniBayiBalitaItems,
      ...upayaPencegahanItems,
    ]) {
      if (item.slug == itemSlug) {
        return item;
      }
    }
    return null;
  }
}

class KebutuhanMuMockItem {
  const KebutuhanMuMockItem({
    required this.slug,
    required this.name,
    required this.subtitle,
    this.isCalculator = false,
  });

  final String slug;
  final String name;
  final String subtitle;
  final bool isCalculator;
}
