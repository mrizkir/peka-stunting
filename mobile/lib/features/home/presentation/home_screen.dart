import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../auth/providers/auth_provider.dart';
import '../../../core/config/app_config.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/menu_tile.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authStateProvider).value;

    return Scaffold(
      appBar: AppBar(
        title: const Text(AppConfig.appName),
        actions: [
          IconButton(
            onPressed: () async {
              await ref.read(authStateProvider.notifier).logout();
            },
            icon: const Icon(Icons.logout),
            tooltip: 'Logout',
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Halo, ${user?.name ?? 'Pengguna'}',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    user?.roles.join(', ') ?? '',
                    style: TextStyle(color: Colors.grey.shade600),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          MenuTile(
            imageAsset: 'assets/images/icon_mengenal_stunting.png',
            title: 'Mengenal Stunting',
            subtitle: 'Baca materi edukasi sesuai kelompok sasaran.',
            color: AppTheme.primary,
            onTap: () => context.push('/mengenal-stunting'),
          ),
          const SizedBox(height: 12),
          MenuTile(
            icon: Icons.checklist_outlined,
            title: 'Kebutuhanmu',
            subtitle: 'Pilih kelompok sasaran sesuai kebutuhan Anda.',
            color: const Color(0xFF0EA5E9),
            onTap: () => context.push('/kebutuhan-mu'),
          ),
          const SizedBox(height: 12),
          MenuTile(
            icon: Icons.info_outline,
            title: 'Info Aplikasi',
            subtitle: 'Tentang aplikasi PEKA Stunting.',
            color: const Color(0xFF6366F1),
            onTap: () => context.push('/app-info'),
          ),
        ],
      ),
    );
  }
}
