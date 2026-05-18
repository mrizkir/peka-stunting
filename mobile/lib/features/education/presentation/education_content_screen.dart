import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/education_repository.dart';
import '../models/education_models.dart';

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

    return Scaffold(
      appBar: AppBar(title: const Text('Konten')),
      body: contentAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(child: Text(error.toString())),
        data: (content) => ListView(
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
            Text(content.body ?? 'Belum ada isi konten.'),
          ],
        ),
      ),
    );
  }
}
