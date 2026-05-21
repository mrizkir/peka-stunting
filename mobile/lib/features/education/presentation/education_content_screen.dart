import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/education_repository.dart';
import '../models/education_models.dart';
import 'widgets/education_body_html.dart';

final educationContentProvider = FutureProvider.family<
    EducationContentDetail,
    ({String menuSlug, String itemSlug})>((ref, params) {
  return ref.read(educationRepositoryProvider).fetchContent(
        menuSlug: params.menuSlug,
        itemSlug: params.itemSlug,
      );
});

class EducationContentScreen extends ConsumerWidget {
  const EducationContentScreen({
    super.key,
    required this.menuSlug,
    required this.itemSlug,
  });

  final String menuSlug;
  final String itemSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final contentAsync = ref.watch(
      educationContentProvider((menuSlug: menuSlug, itemSlug: itemSlug)),
    );

    final contentKey =
        (menuSlug: menuSlug, itemSlug: itemSlug);

    Future<void> refresh() async {
      ref.invalidate(educationContentProvider(contentKey));
      await ref.read(educationContentProvider(contentKey).future);
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Konten'),
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
            children: [
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
              const Text('Tarik ke bawah atau ketuk ikon refresh untuk coba lagi.'),
            ],
          ),
          data: (content) => ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(20),
            children: [
            if (content.featuredImageUrl != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.network(
                  content.featuredImageUrl!,
                  height: 180,
                  width: double.infinity,
                  fit: BoxFit.cover,
                ),
              ),
            if (content.featuredImageUrl != null) const SizedBox(height: 16),
            Text(
              content.title,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            if (content.excerpt != null && content.excerpt!.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                content.excerpt!,
                style: TextStyle(
                  color: Colors.grey.shade700,
                  fontSize: 16,
                ),
              ),
            ],
            const SizedBox(height: 16),
            EducationBodyHtml(html: content.body),
            ],
          ),
        ),
      ),
    );
  }
}
