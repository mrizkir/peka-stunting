import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/widgets/peka_app_bar.dart';
import '../../../core/widgets/education_poster_viewer.dart';
import '../../../core/widgets/education_video_player.dart';
import '../../kebutuhan_mu/presentation/widgets/kebutuhan_mu_menu_description.dart';
import '../data/app_info_repository.dart';
import '../models/app_info_models.dart';

final appInfoContentProvider = FutureProvider<AppInfoContent>((ref) {
  return ref.read(appInfoRepositoryProvider).fetchContent();
});

class AppInfoScreen extends ConsumerWidget {
  const AppInfoScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final contentAsync = ref.watch(appInfoContentProvider);

    Future<void> refresh() async {
      ref.invalidate(appInfoContentProvider);
      await ref.read(appInfoContentProvider.future);
    }

    return Scaffold(
      appBar: PekaAppBar(
        title: contentAsync.maybeWhen(
          data: (content) => Text(content.title),
          orElse: () => const Text('Info Aplikasi'),
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
                '(Pengaturan → Info aplikasi) dan URL API benar. '
                'Tarik ke bawah atau ketuk refresh untuk coba lagi.',
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

  final AppInfoContent content;
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
