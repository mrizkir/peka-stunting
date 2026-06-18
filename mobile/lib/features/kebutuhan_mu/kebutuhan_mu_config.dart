class KebutuhanMuConfig {
  KebutuhanMuConfig._();

  /// Menu edukasi yang bukan bagian "Pilih Kebutuhanmu".
  static const excludedMenuSlugs = {
    'mengenal-stunting',
    'info-aplikasi',
  };

  static const deteksiDiniSlug = 'deteksi-dini';
  static const upayaPencegahanSlug = 'upaya-pencegahan-stunting';

  static const groupTitles = {
    'remaja-putri': 'Remaja Putri',
    'calon-pengantin': 'Calon Pengantin',
    'ibu-hamil': 'Ibu Hamil',
    'ibu-nifas-dan-menyusui': 'Ibu Nifas dan Menyusui',
    'bayi-dan-balita': 'Bayi dan Balita',
  };

  static String? menuLogoAsset(String menuSlug) {
    switch (menuSlug) {
      case 'remaja-putri':
        return 'assets/images/level_2/remaja_putri.png';
      case 'calon-pengantin':
        return 'assets/images/level_2/calon_pengantin.png';
      case 'ibu-hamil':
        return 'assets/images/level_2/ibu_hamil.png';
      case 'ibu-nifas-dan-menyusui':
        return 'assets/images/level_2/ibu_nifas_menyusui.png';
      case 'bayi-dan-balita':
        return 'assets/images/level_2/bayi_balita.png';
      default:
        return null;
    }
  }

  static String? itemLogoAsset(String slug) {
    switch (slug) {
      // deteksi dini
      case 'cek-imt':
        return 'assets/images/deteksi_dini/cek_imt_logo.png';
      case 'cek-lila':
        return 'assets/images/deteksi_dini/cek_lila_logo.png';
      case 'cek-risiko-anemia':
        return 'assets/images/deteksi_dini/cek_risiko_anemia_logo.png';
      case 'cek-keberhasilan-menyusui':
        return 'assets/images/deteksi_dini/cek_keberhasilan_menyusui_logo.png';
      case 'periksa-status-gizi':
        return 'assets/images/deteksi_dini/cek_status_gizi_logo.png';

      // menu makanan kearifan lokal
      case 'menu-makanan-kearifan-lokal':
      case 'makanan-kearifan-lokal-penambah-produksi-asi':
        return 'assets/images/group/menu_makanan_kearifan_lokal_logo.png';
      case 'tumis-jantung-pisang-bilis-basah':
        return 'assets/images/group/tumis_jantung_pisang_bilis_basah_logo.png';
      case 'pindang-ikan-amoy':
        return 'assets/images/group/pindang_ikan_amoy_logo.png';
      case 'nugget-ikan-kembung':
        return 'assets/images/group/nugget_ikan_kembung_logo.png';
      case 'otak-otak-bilis-basah':
        return 'assets/images/group/otak_otak_bilis_basah_logo.png';
      case 'tim-pindang-ikan-patin-sayuran':
        return 'assets/images/group/tim_pindang_ikan_patin_sayuran_logo.png';
      case 'dadar-telur-ikan-bilis-daun-singkong':
        return 'assets/images/group/dadar_telur_ikan_bilis_daun_singkong_logo.png';
      case 'dimsum-ikan-kembung-tahu-wortel':
        return 'assets/images/group/dimsum_ikan_kembung_tahu_wortel_logo.png';
      case 'tumis-daun-pepaya-bilis-basah':
        return 'assets/images/group/tumis_daun_pepaya_bilis_basah_logo.png';
      case 'bubur-ikan-kembung':
        return 'assets/images/group/bubur_ikan_kembung_logo.png';

      // remaja putri
      case 'pola-gizi-seimbang':
        return 'assets/images/remaja_putri/pola_gizi_seimbang_logo.png';
      case 'cara-cegah-anemia':
        return 'assets/images/remaja_putri/cara_cegah_anemia_logo.png';
      case 'olahraga-rutin':
        return 'assets/images/remaja_putri/olahraga_rutin_logo.png';
      case 'bahaya-begadang':
        return 'assets/images/remaja_putri/bahaya_begadang_logo.png';
      case 'jaga-organ-kesehatan-reproduksi':
        return 'assets/images/remaja_putri/jaga_organ_kesehatan_reproduksi_logo.png';
      case 'kebersihan-diri-dan-lingkungan':
        return 'assets/images/remaja_putri/kebersihan_diri_dan_lingkungan_logo.png';

      // ibu hamil
      case 'penuhi-kebutuhan-nutrisi':
        return 'assets/images/ibu_hamil/penuhi_kebutuhan_nutrisi_logo.png';
      case 'lakukan-pemeriksaan-kehamilan-secara-rutin':
        return 'assets/images/ibu_hamil/lakukan_pemeriksaan_kehamilan_secara_rutin_logo.png';
      case 'jaga-kebersihan-diri':
        return 'assets/images/ibu_hamil/jaga_kebersihan_diri_logo.png';
      case 'hindari-paparan-asap-rokok':
        return 'assets/images/ibu_hamil/hindari_paparan_asap_rokok_logo.png';
      case 'olahraga-secara-rutin':
        return 'assets/images/ibu_hamil/olahraga_secara_rutin_logo.png';
      case 'hindari-stres':
        return 'assets/images/ibu_hamil/hindari_stres_logo.png';
      case 'istirahat-yang-cukup':
        return 'assets/images/ibu_hamil/istirahat_yang_cukup_logo.png';

      // ibu nifas dan menyusui
      case 'terapkan-asi-eksklusif':
        return 'assets/images/ibu_nifas/terapkan_asi_eksklusif_logo.png';
      case 'teknik-meningkatkan-produksi-asi':
        return 'assets/images/ibu_nifas/teknik_meningkatkan_produksi_asi_logo.png';
      case 'penuhi-kebutuhan-gizi-seimbang':
        return 'assets/images/ibu_nifas/penuhi_kebutuhan_gizi_seimbang_logo.png';
      case 'persiapkan-kb':
        return 'assets/images/ibu_nifas/persiapkan_kb_logo.png';

      // calon pengantin
      case 'cegah-anemia':
        return 'assets/images/catin/cegah_anemia_logo.png';
      case 'jaga-alat-reproduksi':
        return 'assets/images/catin/jaga_alat_reproduksi_logo.png';
      case 'rencanakan-kehamilan-dengan-baik':
        return 'assets/images/catin/rencanakan_kehamilan_logo.png';
      case 'persiapkan-1000-hari-pertama-kehidupan':
        return 'assets/images/catin/1000_hari_pertama_kehidupan_logo.png';

      // bayi dan balita
      case 'pemberian-asi':
        return 'assets/images/balita/pemberian_asi_logo.png';
      case 'pemberian-makanan-pendamping-asi-yang-benar':
        return 'assets/images/balita/pemberian_mpasi_benar_logo.png';
      case 'rutin-memantau-pertumbuhan-balita':
        return 'assets/images/balita/rutin_memantau_pertumbuhan_balita_logo.png';
      case 'imunisasi':
        return 'assets/images/balita/imunisasi_logo.png';
      case 'menu-makanan-tambahan-berbasis-kearifan-lokal':
        return 'assets/images/balita/menu_makanan_bayi_logo.png';
      default:
        return null;
    }
  }

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
