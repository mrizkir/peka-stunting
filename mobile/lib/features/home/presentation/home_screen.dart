import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../auth/providers/auth_provider.dart';
import 'widgets/home_featured_menu_card.dart';
import 'widgets/home_profile_card.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authStateProvider).value;

    return Scaffold(
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () => ref.read(authStateProvider.notifier).refreshUser(),
          child: ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(20),
            children: [
              Center(
                child: Image.asset(
                  'assets/images/logo_app_2.png',
                  height: 100,
                  fit: BoxFit.contain,
                ),
              ),
              const SizedBox(height: 16),
              HomeProfileCard(
                name: user?.name ?? 'Pengguna',
                rolesLabel: user?.roles.join(', ') ?? '',
                profilePhotoUrl: user?.profilePhotoUrl,
              ),
              const SizedBox(height: 16),
              HomeFeaturedMenuCard(
                title: 'Mengenal Stunting',
                subtitle: 'Baca materi edukasi sesuai kelompok sasaran.',
                imageAsset: 'assets/images/icon_mengenal_stunting.png',
                onTap: () => context.push('/mengenal-stunting'),
              ),
              const SizedBox(height: 16),
              HomeFeaturedMenuCard(
                imageAsset: 'assets/images/icon_kebutuhan_mu.png',
                title: 'Kebutuhanmu',
                subtitle: 'Pilih kelompok sasaran sesuai kebutuhan Anda.',
                onTap: () => context.push('/kebutuhan-mu'),
              ),
              const SizedBox(height: 16),
              HomeFeaturedMenuCard(
                imageAsset: 'assets/images/info_aplikasi.png',
                title: 'Info Aplikasi',
                subtitle: 'Tentang aplikasi PEKA Stunting.',
                onTap: () => context.push('/app-info'),
              ),
          ],
        ),
      ),
      ),
    );
  }
}
