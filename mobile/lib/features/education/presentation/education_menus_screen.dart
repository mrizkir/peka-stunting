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

    Future<void> refresh() async {
      ref.invalidate(educationMenusProvider);
      await ref.read(educationMenusProvider.future);
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Edukasi'),
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
        child: menusAsync.when(
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
          data: (menus) => ListView.separated(
            physics: const AlwaysScrollableScrollPhysics(),
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
      ),
    );
  }
}
