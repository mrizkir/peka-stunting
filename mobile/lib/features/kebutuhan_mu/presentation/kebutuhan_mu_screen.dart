import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/menu_tile.dart';
import '../data/kebutuhan_mu_repository.dart';
import '../models/kebutuhan_mu_models.dart';

final kebutuhanMuGroupsProvider =
    FutureProvider<List<KebutuhanMuMenuSummary>>((ref) {
  return ref.read(kebutuhanMuRepositoryProvider).fetchTargetGroups();
});

class KebutuhanMuScreen extends ConsumerWidget {
  const KebutuhanMuScreen({super.key});

  static const _fallbackGroups = [
    _FallbackGroup(
      slug: 'remaja-putri',
      name: 'Remaja Putri',
      subtitle: 'Materi edukasi untuk remaja putri.',
    ),
    _FallbackGroup(
      slug: 'calon-pengantin',
      name: 'Calon Pengantin',
      subtitle: 'Materi edukasi untuk calon pengantin.',
    ),
    _FallbackGroup(
      slug: 'ibu-hamil',
      name: 'Ibu Hamil',
      subtitle: 'Materi edukasi untuk ibu hamil.',
    ),
    _FallbackGroup(
      slug: 'ibu-nifas-dan-menyusui',
      name: 'Ibu Nifas dan Menyusui',
      subtitle: 'Materi edukasi untuk ibu nifas dan menyusui.',
    ),
    _FallbackGroup(
      slug: 'bayi-dan-balita',
      name: 'Bayi dan Balita',
      subtitle: 'Materi edukasi untuk bayi dan balita.',
    ),
  ];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final groupsAsync = ref.watch(kebutuhanMuGroupsProvider);

    Future<void> refresh() async {
      ref.invalidate(kebutuhanMuGroupsProvider);
      await ref.read(kebutuhanMuGroupsProvider.future);
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Kebutuhanmu'),
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
        child: groupsAsync.when(
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
          data: (groups) {
            final tiles = groups.isEmpty
                ? _fallbackGroups
                    .asMap()
                    .entries
                    .map((entry) {
                      final index = entry.key;
                      final group = entry.value;
                      return [
                        if (index > 0) const SizedBox(height: 12),
                        _buildFallbackTile(context, group),
                      ];
                    })
                    .expand((widgets) => widgets)
                    .toList()
                : groups
                    .asMap()
                    .entries
                    .map((entry) {
                      final index = entry.key;
                      final group = entry.value;
                      return [
                        if (index > 0) const SizedBox(height: 12),
                        _buildGroupTile(context, group),
                      ];
                    })
                    .expand((widgets) => widgets)
                    .toList();

            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(20),
              children: [
                Text(
                  'Pilih kelompok sasaran sesuai kebutuhan Anda.',
                  style: TextStyle(color: Colors.grey.shade700),
                ),
                const SizedBox(height: 16),
                ...tiles,
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildGroupTile(
    BuildContext context,
    KebutuhanMuMenuSummary group,
  ) {
    final style = _tileStyle(group.slug);

    return MenuTile(
      icon: style.icon,
      title: group.name,
      subtitle: group.publishedContentsCount > 0
          ? '${group.publishedContentsCount} konten siap baca'
          : style.subtitle,
      color: style.color,
      onTap: () => context.push('/kebutuhan-mu/${group.slug}'),
    );
  }

  Widget _buildFallbackTile(BuildContext context, _FallbackGroup group) {
    final style = _tileStyle(group.slug);

    return MenuTile(
      icon: style.icon,
      title: group.name,
      subtitle: group.subtitle,
      color: style.color,
      onTap: () => context.push('/kebutuhan-mu/${group.slug}'),
    );
  }

  _GroupTileStyle _tileStyle(String slug) {
    switch (slug) {
      case 'remaja-putri':
        return _GroupTileStyle(
          icon: Icons.face_3_outlined,
          color: const Color(0xFFEC4899),
          subtitle: 'Materi edukasi untuk remaja putri.',
        );
      case 'calon-pengantin':
        return _GroupTileStyle(
          icon: Icons.favorite_outline,
          color: const Color(0xFFF43F5E),
          subtitle: 'Materi edukasi untuk calon pengantin.',
        );
      case 'ibu-hamil':
        return _GroupTileStyle(
          icon: Icons.pregnant_woman_outlined,
          color: const Color(0xFF8B5CF6),
          subtitle: 'Materi edukasi untuk ibu hamil.',
        );
      case 'ibu-nifas-dan-menyusui':
        return _GroupTileStyle(
          icon: Icons.child_friendly_outlined,
          color: const Color(0xFF0EA5E9),
          subtitle: 'Materi edukasi untuk ibu nifas dan menyusui.',
        );
      case 'bayi-dan-balita':
        return _GroupTileStyle(
          icon: Icons.baby_changing_station_outlined,
          color: AppTheme.primary,
          subtitle: 'Materi edukasi untuk bayi dan balita.',
        );
      default:
        return _GroupTileStyle(
          icon: Icons.groups_outlined,
          color: const Color(0xFF6366F1),
          subtitle: 'Baca materi edukasi.',
        );
    }
  }
}

class _FallbackGroup {
  const _FallbackGroup({
    required this.slug,
    required this.name,
    required this.subtitle,
  });

  final String slug;
  final String name;
  final String subtitle;
}

class _GroupTileStyle {
  const _GroupTileStyle({
    required this.icon,
    required this.color,
    required this.subtitle,
  });

  final IconData icon;
  final Color color;
  final String subtitle;
}
