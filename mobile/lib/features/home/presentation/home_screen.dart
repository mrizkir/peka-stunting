import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../auth/providers/auth_provider.dart';
import '../../../core/config/app_config.dart';
import '../../../core/theme/app_theme.dart';
import 'widgets/home_colored_menu_card.dart';
import 'widgets/home_featured_menu_card.dart';
import 'widgets/home_profile_card.dart';

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
          HomeProfileCard(
            name: user?.name ?? 'Pengguna',
            rolesLabel: user?.roles.join(', ') ?? '',
          ),
          const SizedBox(height: 16),
          HomeFeaturedMenuCard(
            title: 'Mengenal Stunting',
            subtitle: 'Baca materi edukasi sesuai kelompok sasaran.',
            imageAsset: 'assets/images/icon_mengenal_stunting.png',
            onTap: () => context.push('/mengenal-stunting'),
          ),
          const SizedBox(height: 12),
          HomeColoredMenuCard(
            icon: Icons.checklist_outlined,
            title: 'Kebutuhanmu',
            subtitle: 'Pilih kelompok sasaran sesuai kebutuhan Anda.',
            backgroundColor: AppTheme.kebutuhanMuCardBackground,
            onTap: () => context.push('/kebutuhan-mu'),
          ),
          const SizedBox(height: 12),
          HomeColoredMenuCard(
            imageAsset: 'assets/images/info_aplikasi.png',
            title: 'Info Aplikasi',
            subtitle: 'Tentang aplikasi PEKA Stunting.',
            backgroundColor: AppTheme.infoAppCardBackground,
            foregroundColor: const Color(0xFF0F172A),
            onTap: () => context.push('/app-info'),
          ),
        ],
      ),
    );
  }
}
