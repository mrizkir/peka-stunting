import 'package:flutter/material.dart';

import '../kebutuhan_mu_mock_data.dart';

/// Layar placeholder konten / kalkulator (mockup navigasi).
class KebutuhanMuContentScreen extends StatelessWidget {
  const KebutuhanMuContentScreen({
    super.key,
    required this.menuSlug,
    required this.itemSlug,
  });

  final String menuSlug;
  final String itemSlug;

  @override
  Widget build(BuildContext context) {
    final item = KebutuhanMuMockData.findItem(itemSlug);
    final title = item?.name ?? 'Materi';

    return Scaffold(
      appBar: AppBar(
        title: Text(title),
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    item != null
                        ? 'Halaman mockup — konten atau kalkulator '
                            '${item.name} akan ditambahkan di sini.'
                        : 'Halaman mockup — materi akan ditambahkan.',
                    style: TextStyle(
                      color: Colors.grey.shade700,
                      height: 1.5,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
