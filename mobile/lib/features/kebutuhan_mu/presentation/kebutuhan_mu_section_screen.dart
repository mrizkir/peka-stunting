import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/menu_tile.dart';
import '../kebutuhan_mu_config.dart';
import '../kebutuhan_mu_mock_data.dart';

class KebutuhanMuSectionScreen extends StatelessWidget {
  const KebutuhanMuSectionScreen({
    super.key,
    required this.menuSlug,
    required this.sectionSlug,
  });

  final String menuSlug;
  final String sectionSlug;

  KebutuhanMuSectionConfig? get _sectionConfig {
    for (final section in KebutuhanMuConfig.sections) {
      if (section.slug == sectionSlug) {
        return section;
      }
    }
    return null;
  }

  @override
  Widget build(BuildContext context) {
    final config = _sectionConfig;
    final items = KebutuhanMuMockData.itemsForSection(sectionSlug);

    return Scaffold(
      appBar: AppBar(
        title: Text(config?.title ?? 'Materi'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          if (config != null) ...[
            Text(
              config.subtitle,
              style: TextStyle(color: Colors.grey.shade700),
            ),
            const SizedBox(height: 16),
          ],
          if (items.isEmpty)
            const Text('Materi akan ditambahkan.')
          else
            for (var i = 0; i < items.length; i++) ...[
              if (i > 0) const SizedBox(height: 12),
              _buildItemTile(context, items[i]),
            ],
        ],
      ),
    );
  }

  Widget _buildItemTile(BuildContext context, KebutuhanMuMockItem item) {
    return MenuTile(
      icon: item.isCalculator
          ? Icons.calculate_outlined
          : Icons.article_outlined,
      title: item.name,
      subtitle: item.subtitle,
      color: item.isCalculator ? const Color(0xFF0EA5E9) : AppTheme.primary,
      onTap: () => context.push(
        '/kebutuhan-mu/$menuSlug/$sectionSlug/${item.slug}',
      ),
    );
  }
}
