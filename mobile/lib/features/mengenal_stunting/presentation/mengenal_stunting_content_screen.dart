import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/mengenal_stunting_repository.dart';
import '../models/mengenal_stunting_models.dart';
import 'widgets/mengenal_stunting_body_html.dart';

final mengenalStuntingContentProvider = FutureProvider.family<
    MengenalStuntingContent,
    String>((ref, itemSlug) {
  return ref.read(mengenalStuntingRepositoryProvider).fetchContent(itemSlug);
});

class MengenalStuntingContentScreen extends ConsumerWidget {
  const MengenalStuntingContentScreen({
    super.key,
    required this.itemSlug,
  });

  final String itemSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final contentAsync = ref.watch(mengenalStuntingContentProvider(itemSlug));

    Future<void> refresh() async {
      ref.invalidate(mengenalStuntingContentProvider(itemSlug));
      await ref.read(mengenalStuntingContentProvider(itemSlug).future);
    }

    return Scaffold(
      appBar: AppBar(
        title: contentAsync.maybeWhen(
          data: (content) => Text(content.title),
          orElse: () => const Text('Materi'),
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
                'Tarik ke bawah atau ketuk ikon refresh untuk coba lagi.',
              ),
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
              MengenalStuntingBodyHtml(html: content.body),
            ],
          ),
        ),
      ),
    );
  }
}
