import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../deteksi_dini/presentation/cek_imt_screen.dart';
import '../../deteksi_dini/presentation/cek_lila_screen.dart';
import '../../deteksi_dini/presentation/cek_risiko_anemia_screen.dart';
import '../../deteksi_dini/presentation/periksa_status_gizi_screen.dart';
import '../../../core/widgets/education_poster_viewer.dart';
import '../../../core/widgets/education_video_player.dart';
import '../data/kebutuhan_mu_repository.dart';
import '../kebutuhan_mu_mock_data.dart';
import '../models/kebutuhan_mu_models.dart';
import 'widgets/kebutuhan_mu_menu_description.dart';

typedef KebutuhanMuContentArgs = ({String menuSlug, String itemSlug});

final kebutuhanMuContentDetailProvider = FutureProvider.family<
    KebutuhanMuContent,
    KebutuhanMuContentArgs>((ref, args) {
  return ref.read(kebutuhanMuRepositoryProvider).fetchContent(
        menuSlug: args.menuSlug,
        itemSlug: args.itemSlug,
      );
});

/// Dispatcher: kalkulator vs materi dari API (poster + teks).
class KebutuhanMuContentScreen extends ConsumerWidget {
  const KebutuhanMuContentScreen({
    super.key,
    required this.menuSlug,
    required this.itemSlug,
  });

  final String menuSlug;
  final String itemSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    switch (itemSlug) {
      case 'cek-imt':
        return CekImtScreen(menuSlug: menuSlug);
      case 'cek-lila':
        return CekLilaScreen(menuSlug: menuSlug);
      case 'cek-risiko-anemia':
        return CekRisikoAnemiaScreen(menuSlug: menuSlug);
      case 'periksa-status-gizi':
        return PeriksaStatusGiziScreen(menuSlug: menuSlug);
      default:
        break;
    }

    final args = (menuSlug: menuSlug, itemSlug: itemSlug);
    final contentAsync = ref.watch(kebutuhanMuContentDetailProvider(args));
    final mockItem = KebutuhanMuMockData.findItem(itemSlug);

    Future<void> refresh() async {
      ref.invalidate(kebutuhanMuContentDetailProvider(args));
      await ref.read(kebutuhanMuContentDetailProvider(args).future);
    }

    return Scaffold(
      appBar: AppBar(
        title: contentAsync.maybeWhen(
          data: (content) => Text(content.title),
          orElse: () => Text(mockItem?.name ?? 'Materi'),
        ),
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
        child: contentAsync.when(
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
              Text(error.toString()),
              const SizedBox(height: 16),
              const Text(
                'Pastikan konten sudah dipublikasikan di CMS '
                '(Kelola edukasi) dan URL API benar. Tarik ke bawah '
                'atau ketuk refresh untuk coba lagi.',
              ),
            ],
          ),
          data: (content) {
            return LayoutBuilder(
              builder: (context, constraints) {
                return _ContentWithPosters(
                  content: content,
                  posterHeight: constraints.maxHeight,
                );
              },
            );
          },
        ),
      ),
    );
  }
}

class _ContentWithPosters extends StatelessWidget {
  const _ContentWithPosters({
    required this.content,
    required this.posterHeight,
  });

  final KebutuhanMuContent content;
  final double posterHeight;

  @override
  Widget build(BuildContext context) {
    final hasVideo = content.videoUrl != null;
    final hasExcerpt = content.excerpt?.trim().isNotEmpty ?? false;
    final hasBodyText = content.body?.trim().isNotEmpty ?? false;

    return EducationPosterContentLayout(
      posterUrls: content.posterImages,
      posterHeight: posterHeight,
      emptyState: Padding(
        padding: const EdgeInsets.only(top: 16),
        child: Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Text(
              'Belum ada poster atau teks. Unggah galeri poster '
              'di halaman CMS untuk materi ini.',
              style: TextStyle(
                color: Colors.grey.shade700,
                height: 1.5,
              ),
            ),
          ),
        ),
      ),
      contentChildren: [
        if (hasVideo) ...[
          EducationVideoPlayer(videoUrl: content.videoUrl!),
          const SizedBox(height: 16),
        ],
        if (hasExcerpt) ...[
          const SizedBox(height: 12),
          Text(
            content.excerpt!,
            style: TextStyle(
              color: Colors.grey.shade700,
              fontSize: 16,
            ),
          ),
        ],
        if (hasBodyText) ...[
          const SizedBox(height: 16),
          KebutuhanMuMenuDescription(description: content.body!),
        ],
      ],
    );
  }
}
