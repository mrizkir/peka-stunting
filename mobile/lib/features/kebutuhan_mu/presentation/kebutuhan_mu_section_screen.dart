import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/peka_app_bar.dart';
import '../../../core/widgets/menu_tile.dart';
import '../models/kebutuhan_mu_models.dart';
import 'kebutuhan_mu_menu_screen.dart';

class KebutuhanMuSectionScreen extends ConsumerWidget {
  const KebutuhanMuSectionScreen({
    super.key,
    required this.menuSlug,
    required this.sectionSlug,
  });

  final String menuSlug;
  final String sectionSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final menuDetailAsync = ref.watch(kebutuhanMuMenuDetailProvider(menuSlug));

    Future<void> refresh() async {
      ref.invalidate(kebutuhanMuMenuDetailProvider(menuSlug));
      await ref.read(kebutuhanMuMenuDetailProvider(menuSlug).future);
    }

    return Scaffold(
      appBar: PekaAppBar(
        title: Text(
          menuDetailAsync.maybeWhen(
            data: (snapshot) =>
                snapshot?.data.sections
                    .where((section) => section.slug == sectionSlug)
                    .firstOrNull
                    ?.name ??
                'Materi',
            orElse: () => 'Materi',
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: refresh,
        child: menuDetailAsync.when(
          loading: () => ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            children: const [
              SizedBox(height: 200),
              Center(child: CircularProgressIndicator()),
            ],
          ),
          error: (error, _) => ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(20),
            children: [
              Text('Gagal memuat section: $error'),
            ],
          ),
          data: (snapshot) {
            final section = snapshot?.data.sections
                .where((candidate) => candidate.slug == sectionSlug)
                .firstOrNull;

            if (section == null) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                children: const [
                  Text('Section tidak ditemukan.'),
                ],
              );
            }

            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(20),
              children: [
                Text(
                  '${section.items.length} materi tersedia.',
                  style: TextStyle(color: Colors.grey.shade700),
                ),
                const SizedBox(height: 16),
                if (section.items.isEmpty)
                  const Text('Materi akan ditambahkan.')
                else
                  for (var i = 0; i < section.items.length; i++) ...[
                    if (i > 0) const SizedBox(height: 12),
                    _buildItemTile(context, section.items[i]),
                  ],
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildItemTile(BuildContext context, KebutuhanMuItem item) {
    if (item.isGroup) {
      final imageAsset = _itemImageAsset(item.slug);

      return MenuTile(
        icon: imageAsset == null ? Icons.restaurant_menu_outlined : null,
        imageAsset: imageAsset,
        title: item.name,
        subtitle: '${item.items.length} resep tersedia',
        color: AppTheme.primary,
        backgroundColor: const Color(0xFFE1ECC8),
        onTap: () => context.push(
          '/kebutuhan-mu/$menuSlug/$sectionSlug/group/${item.slug}',
        ),
      );
    }

    final isCalculator = item.isCalculator;
    final imageAsset = _itemImageAsset(item.slug);

    return MenuTile(
      icon: imageAsset == null
          ? (isCalculator
              ? Icons.calculate_outlined
              : Icons.article_outlined)
          : null,
      imageAsset: imageAsset,
      title: item.name,
      subtitle: isCalculator
          ? 'Silahkan tes untuk skrining'
          : 'Baca materi edukasi',
      color: isCalculator ? const Color(0xFF0EA5E9) : AppTheme.primary,
      backgroundColor: const Color(0xFFE1ECC8),
      onTap: () => context.push(
        '/kebutuhan-mu/$menuSlug/$sectionSlug/${item.slug}',
      ),
    );
  }

  String? _itemImageAsset(String slug) {
    switch (slug) {
      //deteksi dini
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
      
      //group menu makanan kearifan lokal
      case 'menu-makanan-kearifan-lokal':
        return 'assets/images/group/menu_makanan_kearifan_lokal_logo.png';
      
      //remaja putri
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
      
      //ibu hamil
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
      
      //ibu nifas dan menyusui
      case 'terapkan-asi-eksklusif':
        return 'assets/images/ibu_nifas/terapkan_asi_eksklusif_logo.png';
      case 'teknik-meningkatkan-produksi-asi':
        return 'assets/images/ibu_nifas/teknik_meningkatkan_produksi_asi_logo.png';
      case 'penuhi-kebutuhan-gizi-seimbang':
        return 'assets/images/ibu_nifas/penuhi_kebutuhan_gizi_seimbang_logo.png';
      case 'persiapkan-kb':
        return 'assets/images/ibu_nifas/persiapkan_kb_logo.png';
      
      //calon pengantin
      case 'cegah-anemia':
        return 'assets/images/catin/cegah_anemia_logo.png';
      case 'jaga-alat-reproduksi':
        return 'assets/images/catin/jaga_alat_reproduksi_logo.png';
      case 'rencanakan-kehamilan-dengan-baik':
        return 'assets/images/catin/rencanakan_kehamilan_logo.png';
      case 'persiapkan-1000-hari-pertama-kehidupan':
        return 'assets/images/catin/1000_hari_pertama_kehidupan_logo.png';

      //bayi dan balita
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
}
