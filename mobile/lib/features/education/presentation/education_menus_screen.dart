import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../data/education_repository.dart';
import '../models/education_models.dart';

final educationMenusProvider = FutureProvider<List<EducationMenu>>((ref) {
  return ref.read(educationRepositoryProvider).fetchMenus();
});

class EducationMenusScreen extends ConsumerWidget {
  const EducationMenusScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final menusAsync = ref.watch(educationMenusProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Edukasi')),
      body: menusAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(child: Text(error.toString())),
        data: (menus) => ListView.separated(
          padding: const EdgeInsets.all(16),
          itemCount: menus.length,
          separatorBuilder: (_, __) => const SizedBox(height: 12),
          itemBuilder: (context, index) {
            final menu = menus[index];
            return Card(
              child: ListTile(
                title: Text(menu.name),
                subtitle: Text('${menu.publishedContentsCount} konten siap baca'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => context.push('/education/${menu.slug}'),
              ),
            );
          },
        ),
      ),
    );
  }
}
