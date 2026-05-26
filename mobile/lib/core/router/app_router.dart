import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/presentation/login_screen.dart';
import '../../features/splash/presentation/splash_screen.dart';
import '../../features/auth/presentation/register_screen.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../features/home/presentation/app_info_screen.dart';
import '../../features/kebutuhan_mu/presentation/kebutuhan_mu_content_screen.dart';
import '../../features/kebutuhan_mu/presentation/kebutuhan_mu_menu_screen.dart';
import '../../features/kebutuhan_mu/presentation/kebutuhan_mu_screen.dart';
import '../../features/kebutuhan_mu/presentation/kebutuhan_mu_section_screen.dart';
import '../../features/mengenal_stunting/presentation/mengenal_stunting_content_screen.dart';
import '../../features/mengenal_stunting/presentation/mengenal_stunting_screen.dart';
import '../../features/home/presentation/home_screen.dart';

final _rootNavigatorKey = GlobalKey<NavigatorState>();

final appRouterProvider = Provider<GoRouter>((ref) {
  return GoRouter(
    navigatorKey: _rootNavigatorKey,
    initialLocation: '/splash',
    refreshListenable: _AuthRefreshListenable(ref),
    redirect: (context, state) {
      final authState = ref.read(authStateProvider);
      final location = state.matchedLocation;
      final user = authState.valueOrNull;
      final isSplash = location == '/splash';
      final isAuthRoute =
          location == '/login' || location == '/register';

      if (isSplash) {
        return null;
      }

      if (authState.isLoading) {
        return null;
      }

      if (user == null) {
        return isAuthRoute ? null : '/login';
      }

      if (isAuthRoute) {
        return '/';
      }

      return null;
    },
    routes: [
      GoRoute(
        path: '/splash',
        builder: (context, state) => const SplashScreen(),
      ),
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/register',
        builder: (context, state) => const RegisterScreen(),
      ),
      GoRoute(
        path: '/',
        builder: (context, state) => const HomeScreen(),
      ),
      GoRoute(
        path: '/app-info',
        builder: (context, state) => const AppInfoScreen(),
      ),
      GoRoute(
        path: '/mengenal-stunting',
        builder: (context, state) => const MengenalStuntingScreen(),
      ),
      GoRoute(
        path: '/mengenal-stunting/:itemSlug',
        builder: (context, state) => MengenalStuntingContentScreen(
          itemSlug: state.pathParameters['itemSlug']!,
        ),
      ),
      GoRoute(
        path: '/kebutuhan-mu',
        builder: (context, state) => const KebutuhanMuScreen(),
      ),
      GoRoute(
        path: '/kebutuhan-mu/:menuSlug/:sectionSlug/:itemSlug',
        builder: (context, state) => KebutuhanMuContentScreen(
          menuSlug: state.pathParameters['menuSlug']!,
          itemSlug: state.pathParameters['itemSlug']!,
        ),
      ),
      GoRoute(
        path: '/kebutuhan-mu/:menuSlug/:sectionSlug',
        builder: (context, state) => KebutuhanMuSectionScreen(
          menuSlug: state.pathParameters['menuSlug']!,
          sectionSlug: state.pathParameters['sectionSlug']!,
        ),
      ),
      GoRoute(
        path: '/kebutuhan-mu/:menuSlug',
        builder: (context, state) => KebutuhanMuMenuScreen(
          menuSlug: state.pathParameters['menuSlug']!,
        ),
      ),
    ],
  );
});

class _AuthRefreshListenable extends ChangeNotifier {
  _AuthRefreshListenable(this._ref) {
    _ref.listen(authStateProvider, (_, __) => notifyListeners());
  }

  final Ref _ref;
}
