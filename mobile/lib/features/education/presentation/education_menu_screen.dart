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

    return Scaffold(
      appBar: AppBar(title: const Text('Submenu')),
      body: detailAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(child: Text(error.toString())),
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
    );
  }
}
