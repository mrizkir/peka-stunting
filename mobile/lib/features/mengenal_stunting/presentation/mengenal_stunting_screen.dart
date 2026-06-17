import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/peka_app_bar.dart';
import '../../../core/widgets/menu_tile.dart';
import '../data/mengenal_stunting_repository.dart';
import '../models/mengenal_stunting_models.dart';

final mengenalStuntingMenuProvider =
    FutureProvider<MengenalStuntingMenu>((ref) {
  return ref.read(mengenalStuntingRepositoryProvider).fetchMenu();
});

class MengenalStuntingScreen extends ConsumerWidget {
  const MengenalStuntingScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final menuAsync = ref.watch(mengenalStuntingMenuProvider);

    Future<void> refresh() async {
      ref.invalidate(mengenalStuntingMenuProvider);
      await ref.read(mengenalStuntingMenuProvider.future);
    }

    return Scaffold(
      appBar: PekaAppBar(
        title: const Text('Mengenal Stunting'),
        actions: [
          IconButton(
            onPressed: () => refresh(),
            tooltip: 'Muat ulang',
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: refresh,
        child: menuAsync.when(
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
            children: [Text(error.toString())],
          ),
          data: (menu) {
            if (menu.items.isEmpty) {
              return ListView(
                padding: const EdgeInsets.all(20),
                children: [                                    
                  MenuTile(
                    imageAsset: 'assets/images/level_1/icon_mengenal_stunting.png',
                    title: 'Pengertian Stunting',
                    subtitle: 'Memahami pengertian stunting.',
                    color: AppTheme.primary,
                    backgroundColor: const Color(0xFFE1ECC8),
                    onTap: () => context.push('/mengenal-stunting/pengertian'),
                  ),
                  const SizedBox(height: 12),
                  MenuTile(
                    imageAsset: 'assets/images/level_1/icon_mengenal_stunting.png',
                    title: 'Ciri - Ciri Stunting',
                    subtitle: 'Mengenali ciri-ciri stunting.',
                    color: const Color(0xFF6366F1),
                    backgroundColor: const Color(0xFFE1ECC8),
                    onTap: () => context.push('/mengenal-stunting/ciri-ciri'),
                  ),
                  const SizedBox(height: 12),
                  MenuTile(
                    imageAsset: 'assets/images/level_1/icon_mengenal_stunting.png',
                    title: 'Penyebab Stunting',
                    subtitle: 'Mengenali penyebab stunting.',
                    color: const Color(0xFF6366F1),
                    backgroundColor: const Color(0xFFE1ECC8),
                    onTap: () => context.push('/mengenal-stunting/penyebab'),
                  ),
                  MenuTile(
                    imageAsset: 'assets/images/level_1/icon_mengenal_stunting.png',
                    title: 'Siapa yang Berisiko',
                    subtitle: 'Mengenali siapa yang berisiko stunting.',
                    color: const Color(0xFF6366F1),
                    backgroundColor: const Color(0xFFE1ECC8),
                    onTap: () => context.push('/mengenal-stunting/siapa-yang-berisiko'),
                  ),
                  MenuTile(
                    imageAsset: 'assets/images/level_1/icon_mengenal_stunting.png',
                    title: 'Dampak Stunting',
                    subtitle: 'Mengenali siapa yang berisiko stunting.',
                    color: const Color(0xFF6366F1),
                    backgroundColor: const Color(0xFFE1ECC8),
                    onTap: () => context.push('/mengenal-stunting/dampak'),
                  ),
                ],
              );
            }

            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(20),
              children: [
                for (var i = 0; i < menu.items.length; i++) ...[
                  if (i > 0) const SizedBox(height: 12),
                  _buildItemTile(context, menu.items[i]),
                ],
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildItemTile(BuildContext context, MengenalStuntingItem item) {
    final style = _tileStyle(item.slug);

    return MenuTile(
      icon: style.icon,
      title: item.name,
      subtitle: item.excerpt?.trim().isNotEmpty == true
          ? item.excerpt!
          : style.subtitle,
      color: style.color,
      backgroundColor: const Color(0xFFE1ECC8),
      onTap: () => context.push('/mengenal-stunting/${item.slug}'),
    );
  }

  _ItemTileStyle _tileStyle(String slug) {
    switch (slug) {
      case 'pengertian':
        return _ItemTileStyle(
          icon: Icons.menu_book_outlined,
          color: AppTheme.primary,
          subtitle: 'Memahami pengertian stunting.',
        );
      case 'ciri-ciri':
        return _ItemTileStyle(
          icon: Icons.fact_check_outlined,
          color: const Color(0xFF0EA5E9),
          subtitle: 'Mengenali ciri-ciri stunting.',
        );
      default:
        return _ItemTileStyle(
          icon: Icons.article_outlined,
          color: const Color(0xFF6366F1),
          subtitle: 'Baca materi edukasi.',
        );
    }
  }
}

class _ItemTileStyle {
  const _ItemTileStyle({
    required this.icon,
    required this.color,
    required this.subtitle,
  });

  final IconData icon;
  final Color color;
  final String subtitle;
}
