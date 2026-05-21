import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/menu_tile.dart';
import '../kebutuhan_mu_config.dart';
import '../kebutuhan_mu_mock_data.dart';

class KebutuhanMuMenuScreen extends StatelessWidget {
  const KebutuhanMuMenuScreen({
    super.key,
    required this.menuSlug,
  });

  final String menuSlug;

  String get _groupTitle {
    const titles = {
      'remaja-putri': 'Remaja Putri',
      'calon-pengantin': 'Calon Pengantin',
      'ibu-hamil': 'Ibu Hamil',
      'ibu-nifas-dan-menyusui': 'Ibu Nifas dan Menyusui',
      'bayi-dan-balita': 'Bayi dan Balita',
    };
    return titles[menuSlug] ?? 'Kebutuhanmu';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_groupTitle),
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          for (var i = 0; i < KebutuhanMuConfig.sections.length; i++) ...[
            if (i > 0) const SizedBox(height: 12),
            _buildSectionTile(
              context,
              config: KebutuhanMuConfig.sections[i],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildSectionTile(
    BuildContext context, {
    required KebutuhanMuSectionConfig config,
  }) {
    final style = _sectionStyle(config.iconName);
    final itemCount = KebutuhanMuMockData.itemsForSection(config.slug).length;
    final subtitle = itemCount > 0
        ? '$itemCount materi'
        : config.subtitle;

    return MenuTile(
      icon: style.icon,
      title: config.title,
      subtitle: subtitle,
      color: style.color,
      onTap: () => context.push('/kebutuhan-mu/$menuSlug/${config.slug}'),
    );
  }

  _SectionTileStyle _sectionStyle(String iconName) {
    switch (iconName) {
      case 'deteksi':
        return _SectionTileStyle(
          icon: Icons.monitor_heart_outlined,
          color: const Color(0xFF0EA5E9),
        );
      case 'upaya':
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
}

class _SectionTileStyle {
  const _SectionTileStyle({
    required this.icon,
    required this.color,
  });

  final IconData icon;
  final Color color;
}
