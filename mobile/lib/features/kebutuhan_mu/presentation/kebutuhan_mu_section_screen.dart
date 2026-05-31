import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
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

    return Scaffold(
      appBar: AppBar(
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
      body: menuDetailAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Text('Gagal memuat section: $error'),
          ),
        ),
        data: (snapshot) {
          final section = snapshot?.data.sections
              .where((candidate) => candidate.slug == sectionSlug)
              .firstOrNull;

          if (section == null) {
            return const Center(
              child: Padding(
                padding: EdgeInsets.all(20),
                child: Text('Section tidak ditemukan.'),
              ),
            );
          }

          return ListView(
            padding: const EdgeInsets.all(20),
            children: [
              Text(
                '${section.items.length} materi tersedia.',
                style: TextStyle(color: Colors.grey.shade700),
              ),
              if (snapshot?.isFromCache == true) ...[
                const SizedBox(height: 6),
                Text(
                  'Konten tersimpan (offline).',
                  style: TextStyle(
                    color: Colors.amber.shade900,
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
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
    );
  }

  Widget _buildItemTile(BuildContext context, KebutuhanMuItem item) {
    final isCalculator = item.type == 'calculator';

    return MenuTile(
      icon: isCalculator
          ? Icons.calculate_outlined
          : Icons.article_outlined,
      title: item.name,
      subtitle: item.excerpt?.trim().isNotEmpty == true
          ? item.excerpt!
          : 'Buka materi',
      color: isCalculator ? const Color(0xFF0EA5E9) : AppTheme.primary,
      onTap: () => context.push(
        '/kebutuhan-mu/$menuSlug/$sectionSlug/${item.slug}',
      ),
    );
  }
}
