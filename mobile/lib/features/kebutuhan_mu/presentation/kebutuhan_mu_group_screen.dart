import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/menu_tile.dart';
import '../../../core/widgets/peka_app_bar.dart';
import '../kebutuhan_mu_config.dart';
import '../models/kebutuhan_mu_models.dart';
import 'kebutuhan_mu_menu_screen.dart';

class KebutuhanMuGroupScreen extends ConsumerWidget {
  const KebutuhanMuGroupScreen({
    super.key,
    required this.menuSlug,
    required this.sectionSlug,
    required this.groupSlug,
  });

  final String menuSlug;
  final String sectionSlug;
  final String groupSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final menuDetailAsync = ref.watch(kebutuhanMuMenuDetailProvider(menuSlug));

    Future<void> refresh() async {
      ref.invalidate(kebutuhanMuMenuDetailProvider(menuSlug));
      await ref.read(kebutuhanMuMenuDetailProvider(menuSlug).future);
    }

    return Scaffold(
      appBar: PekaAppBar(
        logoAssetPath: KebutuhanMuConfig.menuLogoAsset(menuSlug),
        title: Text(
          menuDetailAsync.maybeWhen(
            data: (snapshot) => _findGroup(snapshot?.data)?.name ?? 'Materi',
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
              Text('Gagal memuat materi: $error'),
            ],
          ),
          data: (snapshot) {
            final group = _findGroup(snapshot?.data);

            if (group == null) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                children: const [
                  Text('Materi tidak ditemukan.'),
                ],
              );
            }

            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(20),
              children: [                
                const SizedBox(height: 16),
                if (group.items.isEmpty)
                  const Text('Resep akan ditambahkan.')
                else
                  for (var i = 0; i < group.items.length; i++) ...[
                    if (i > 0) const SizedBox(height: 12),
                    _buildRecipeTile(context, group.items[i]),
                  ],
              ],
            );
          },
        ),
      ),
    );
  }

  KebutuhanMuItem? _findGroup(KebutuhanMuMenuDetail? detail) {
    if (detail == null) {
      return null;
    }

    final section = detail.sections
        .where((candidate) => candidate.slug == sectionSlug)
        .firstOrNull;

    if (section == null) {
      return null;
    }

    return section.items
        .where((item) => item.isGroup && item.slug == groupSlug)
        .firstOrNull;
  }

  Widget _buildRecipeTile(BuildContext context, KebutuhanMuItem item) {
    final imageAsset = KebutuhanMuConfig.itemLogoAsset(item.slug);
    final fallbackIcon = KebutuhanMuConfig.itemFallbackIcon(item.slug);

    return MenuTile(
      icon: imageAsset == null
          ? (fallbackIcon ?? Icons.restaurant_menu_outlined)
          : null,
      imageAsset: imageAsset,
      title: item.name,
      subtitle: item.excerpt?.trim().isNotEmpty == true
          ? item.excerpt!.trim()
          : 'Lihat resep makanan kearifan lokal. Video demonstrasi dibawahnya.',
      color: AppTheme.primary,
      backgroundColor: const Color(0xFFE1ECC8),
      onTap: () => context.push(
        '/kebutuhan-mu/$menuSlug/$sectionSlug/group/$groupSlug/${item.slug}',
      ),
    );
  }
}
