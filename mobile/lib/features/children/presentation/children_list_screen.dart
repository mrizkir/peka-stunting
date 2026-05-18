import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../data/children_repository.dart';
import '../models/child_models.dart';

final childrenListProvider =
    FutureProvider.family<List<ChildSummary>, String>((ref, query) {
  return ref.read(childrenRepositoryProvider).fetchChildren(query: query);
});

class ChildrenListScreen extends ConsumerStatefulWidget {
  const ChildrenListScreen({super.key});

  @override
  ConsumerState<ChildrenListScreen> createState() => _ChildrenListScreenState();
}

class _ChildrenListScreenState extends ConsumerState<ChildrenListScreen> {
  String _query = '';

  @override
  Widget build(BuildContext context) {
    final childrenAsync = ref.watch(childrenListProvider(_query));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Data Anak'),
        actions: [
          IconButton(
            onPressed: () async {
              await context.push('/children/new');
              ref.invalidate(childrenListProvider(_query));
            },
            icon: const Icon(Icons.add),
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: TextField(
              decoration: const InputDecoration(
                prefixIcon: Icon(Icons.search),
                hintText: 'Cari nama anak...',
              ),
              onSubmitted: (value) => setState(() => _query = value.trim()),
            ),
          ),
          Expanded(
            child: childrenAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (error, _) => Center(child: Text(error.toString())),
              data: (children) {
                if (children.isEmpty) {
                  return const Center(child: Text('Belum ada data anak.'));
                }
                return RefreshIndicator(
                  onRefresh: () async {
                    ref.invalidate(childrenListProvider(_query));
                    await ref.read(childrenListProvider(_query).future);
                  },
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: children.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final child = children[index];
                      return Card(
                        child: ListTile(
                          title: Text(child.name),
                          subtitle: Text(
                            [
                              if (child.village != null) child.village,
                              if (child.latestRisk != null)
                                child.latestRisk!.statusLabel,
                            ].whereType<String>().join(' • '),
                          ),
                          trailing: const Icon(Icons.chevron_right),
                          onTap: () async {
                            await context.push('/children/${child.id}');
                            ref.invalidate(childrenListProvider(_query));
                          },
                        ),
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
