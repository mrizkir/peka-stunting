import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/peka_app_bar.dart';
import '../../../core/widgets/menu_tile.dart';
import '../data/kebutuhan_mu_repository.dart';
import '../kebutuhan_mu_config.dart';
import '../models/kebutuhan_mu_models.dart';
import 'widgets/kebutuhan_mu_menu_description.dart';

final kebutuhanMuMenuDetailProvider =
    StreamProvider.family<KebutuhanMuTaxonomySnapshot<KebutuhanMuMenuDetail>?, String>(
        (ref, menuSlug) {
  return ref.read(kebutuhanMuRepositoryProvider).watchMenuDetail(menuSlug);
});

class KebutuhanMuMenuScreen extends ConsumerWidget {
  const KebutuhanMuMenuScreen({
    super.key,
    required this.menuSlug,
  });

  final String menuSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final menuDetailAsync = ref.watch(kebutuhanMuMenuDetailProvider(menuSlug));

    return Scaffold(
      appBar: PekaAppBar(
        title: Text(
          menuDetailAsync.maybeWhen(
            data: (snapshot) =>
                snapshot?.data.name ??
                KebutuhanMuConfig.groupTitles[menuSlug] ??
                'Kebutuhanmu',
            orElse: () => KebutuhanMuConfig.groupTitles[menuSlug] ?? 'Kebutuhanmu',
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(kebutuhanMuMenuDetailProvider(menuSlug));
          await ref.read(kebutuhanMuMenuDetailProvider(menuSlug).future);
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          children: [
            menuDetailAsync.when(
              loading: () => const Padding(
                padding: EdgeInsets.only(bottom: 16),
                child: LinearProgressIndicator(),
              ),
              error: (error, _) => Padding(
                padding: const EdgeInsets.only(bottom: 16),
                child: Text(
                  'Deskripsi menu belum dapat dimuat: $error',
                  style: TextStyle(color: Colors.grey.shade600),
                ),
              ),
              data: (detail) {
                final detailData = detail?.data;
                final description =
                    detailData?.description ?? _fallbackDescription(menuSlug);
                if (description == null) {
                  return const SizedBox.shrink();
                }

                return Padding(
                  padding: const EdgeInsets.only(bottom: 20),
                  child: KebutuhanMuMenuDescription(
                    description: description,
                  ),
                );
              },
            ),
            menuDetailAsync.when(
              loading: () => const SizedBox.shrink(),
              error: (_, __) => const SizedBox.shrink(),
              data: (snapshot) {
                final detail = snapshot?.data;
                if (detail == null || detail.sections.isEmpty) {
                  return const Text('Section belum tersedia.');
                }

                return Column(
                  children: [
                    for (var i = 0; i < detail.sections.length; i++) ...[
                      if (i > 0) const SizedBox(height: 12),
                      _buildSectionTile(
                        context,
                        section: detail.sections[i],
                      ),
                    ],
                  ],
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionTile(
    BuildContext context, {
    required KebutuhanMuSection section,
  }) {
    final style = _sectionStyle(section.slug);
    final itemCount = section.items.length;
    final subtitle = itemCount > 0
        ? '$itemCount materi'
        : 'Materi akan ditambahkan.';

    return MenuTile(
      icon: style.icon,
      title: section.name,
      subtitle: subtitle,
      color: style.color,
      onTap: () => context.push('/kebutuhan-mu/$menuSlug/${section.slug}'),
    );
  }

  _SectionTileStyle _sectionStyle(String sectionSlug) {
    switch (sectionSlug) {
      case KebutuhanMuConfig.deteksiDiniSlug:
        return _SectionTileStyle(
          icon: Icons.monitor_heart_outlined,
          color: const Color(0xFF0EA5E9),
        );
      case KebutuhanMuConfig.upayaPencegahanSlug:
        return _SectionTileStyle(
          icon: Icons.health_and_safety_outlined,
          color: AppTheme.primary,
        );
      default:
        return _SectionTileStyle(
          icon: Icons.article_outlined,
          color: const Color(0xFF6366F1),
        );
    }
  }

  String? _fallbackDescription(String slug) {
    switch (slug) {
      case 'remaja-putri':
        return 'Hai Remaja Putri, Selamat Datang...\n\n'
            'Remaja putri sebagai calon ibu yang menentukan kesehatan '
            'generasi masa depan sangat penting lho melakukan deteksi dini '
            'dan pencegahan stunting.';
      default:
        return null;
    }
  }
}

class _SectionTileStyle {
  const _SectionTileStyle({
    required this.icon,
    required this.color,
  });

  final IconData icon;
  final Color color;
}
