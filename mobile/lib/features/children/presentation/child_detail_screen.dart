import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../data/children_repository.dart';
import '../models/child_models.dart';

final childDetailProvider =
    FutureProvider.family<ChildDetail, int>((ref, childId) {
  return ref.read(childrenRepositoryProvider).fetchChild(childId);
});

class ChildDetailScreen extends ConsumerWidget {
  const ChildDetailScreen({super.key, required this.childId});

  final int childId;

  Color _riskColor(ColorHint hint) {
    return switch (hint) {
      ColorHint.danger => Colors.red,
      ColorHint.warning => Colors.orange,
      ColorHint.success => Colors.green,
    };
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final childAsync = ref.watch(childDetailProvider(childId));

    return Scaffold(
      appBar: AppBar(title: const Text('Detail Anak')),
      body: childAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(child: Text(error.toString())),
        data: (child) => ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Text(
              child.name,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 8),
            Text('Lahir: ${child.birthDate} • ${child.gender == 'L' ? 'Laki-laki' : 'Perempuan'}'),
            if (child.guardianName != null)
              Text('Wali: ${child.guardianName}'),
            if (child.village != null) Text('Desa: ${child.village}'),
            if (child.posyandu != null) Text('Posyandu: ${child.posyandu}'),
            const SizedBox(height: 20),
            if (child.latestMeasurement != null) ...[
              const Text('Pengukuran terakhir', style: TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Text(
                    'BB: ${child.latestMeasurement!.weightKg} kg • '
                    'TB: ${child.latestMeasurement!.heightCm} cm • '
                    'Umur: ${child.latestMeasurement!.ageMonths} bln',
                  ),
                ),
              ),
            ],
            const SizedBox(height: 16),
            if (child.latestRisk != null) ...[
              const Text('Status risiko', style: TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        child.latestRisk!.statusLabel,
                        style: TextStyle(
                          color: _riskColor(child.latestRisk!.hint),
                          fontWeight: FontWeight.bold,
                          fontSize: 18,
                        ),
                      ),
                      if (child.latestRisk!.summary != null) ...[
                        const SizedBox(height: 8),
                        Text(child.latestRisk!.summary!),
                      ],
                    ],
                  ),
                ),
              ),
            ],
            const SizedBox(height: 24),
            FilledButton.icon(
              onPressed: () async {
                await context.push('/children/$childId/measurement');
                ref.invalidate(childDetailProvider(childId));
              },
              icon: const Icon(Icons.monitor_weight_outlined),
              label: const Text('Input pengukuran'),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: () async {
                try {
                  await ref.read(childrenRepositoryProvider).assessRisk(childId: childId);
                  ref.invalidate(childDetailProvider(childId));
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Penilaian risiko selesai.')),
                    );
                  }
                } catch (error) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(error.toString())),
                    );
                  }
                }
              },
              icon: const Icon(Icons.health_and_safety_outlined),
              label: const Text('Screening risiko'),
            ),
          ],
        ),
      ),
    );
  }
}
