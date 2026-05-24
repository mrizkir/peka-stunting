import 'package:flutter/material.dart';

import '../../deteksi_dini/presentation/cek_imt_screen.dart';
import '../../deteksi_dini/presentation/cek_lila_screen.dart';
import '../../deteksi_dini/presentation/cek_risiko_anemia_screen.dart';
import '../kebutuhan_mu_mock_data.dart';

/// Dispatcher: kalkulator vs halaman materi (mockup).
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

    switch (itemSlug) {
      case 'cek-imt':
        return CekImtScreen(menuSlug: menuSlug);
      case 'cek-lila':
        return CekLilaScreen(menuSlug: menuSlug);
      case 'cek-risiko-anemia':
        return CekRisikoAnemiaScreen(menuSlug: menuSlug);
      default:
        break;
    }

    return _ArticlePlaceholderScreen(
      title: item?.name ?? 'Materi',
      itemName: item?.name,
    );
  }
}

class _ArticlePlaceholderScreen extends StatelessWidget {
  const _ArticlePlaceholderScreen({
    required this.title,
    this.itemName,
  });

  final String title;
  final String? itemName;

  @override
  Widget build(BuildContext context) {
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
                    itemName != null
                        ? 'Halaman mockup — konten ${itemName!} '
                            'akan ditambahkan di sini.'
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
