class KebutuhanMuConfig {
  KebutuhanMuConfig._();

  /// Menu edukasi yang bukan bagian "Pilih Kebutuhanmu".
  static const excludedMenuSlug = 'mengenal-stunting';

  static const deteksiDiniSlug = 'deteksi-dini';
  static const upayaPencegahanSlug = 'upaya-pencegahan-stunting';

  static const groupTitles = {
    'remaja-putri': 'Remaja Putri',
    'calon-pengantin': 'Calon Pengantin',
    'ibu-hamil': 'Ibu Hamil',
    'ibu-nifas-dan-menyusui': 'Ibu Nifas dan Menyusui',
    'bayi-dan-balita': 'Bayi dan Balita',
  };

  static const sections = [
    KebutuhanMuSectionConfig(
      slug: deteksiDiniSlug,
      title: 'Deteksi Dini',
      subtitle: 'Cek dan deteksi dini risiko stunting.',
      iconName: 'deteksi',
    ),
    KebutuhanMuSectionConfig(
      slug: upayaPencegahanSlug,
      title: 'Upaya Pencegahan',
      subtitle: 'Upaya pencegahan stunting sesuai kelompok sasaran.',
      iconName: 'upaya',
    ),
  ];
}

class KebutuhanMuSectionConfig {
  const KebutuhanMuSectionConfig({
    required this.slug,
    required this.title,
    required this.subtitle,
    required this.iconName,
  });

  final String slug;
  final String title;
  final String subtitle;
  final String iconName;
}
