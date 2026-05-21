import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../data/education_repository.dart';
import '../models/education_models.dart';

final educationMenuDetailProvider =
    FutureProvider.family<EducationMenuDetail, String>((ref, menuSlug) {
  return ref.read(educationRepositoryProvider).fetchMenuDetail(menuSlug);
});

class EducationMenuScreen extends ConsumerWidget {
  const EducationMenuScreen({super.key, required this.menuSlug});

  final String menuSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailAsync = ref.watch(educationMenuDetailProvider(menuSlug));

    Future<void> refresh() async {
      ref.invalidate(educationMenuDetailProvider(menuSlug));
      await ref.read(educationMenuDetailProvider(menuSlug).future);
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Submenu'),
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
        child: detailAsync.when(
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
            children: [Text(error.toString())],
          ),
          data: (detail) {
          final sections = [
            ...detail.sections,
            if (detail.items.isNotEmpty)
              EducationSection(
                name: 'Materi',
                slug: 'materi',
                items: detail.items,
              ),
          ];

          return ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(16),
            children: [
              Text(
                detail.name,
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),
              for (final section in sections) ...[
                Text(
                  section.name,
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 16,
                  ),
                ),
                const SizedBox(height: 8),
                ...section.items.map(
                  (item) => Card(
                    child: ListTile(
                      title: Text(item.name),
                      subtitle: Text(item.excerpt ?? item.type),
                      trailing: const Icon(Icons.chevron_right),
                      onTap: () => context.push(
                        '/education/$menuSlug/${item.slug}',
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
              ],
            ],
          );
          },
        ),
      ),
    );
  }
}
