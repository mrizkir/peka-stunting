import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/analytics/analytics_providers.dart';
import '../../features/auth/presentation/forgot_password_screen.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/register_screen.dart';
import '../../features/auth/presentation/reset_password_screen.dart';
import '../../features/splash/presentation/splash_screen.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../features/home/presentation/app_info_screen.dart';
import '../../features/home/presentation/app_shell.dart';
import '../../features/home/presentation/home_screen.dart';
import '../../features/home/presentation/profile_screen.dart';
import '../../features/kebutuhan_mu/presentation/kebutuhan_mu_content_screen.dart';
import '../../features/kebutuhan_mu/presentation/kebutuhan_mu_group_screen.dart';
import '../../features/kebutuhan_mu/presentation/kebutuhan_mu_menu_screen.dart';
import '../../features/kebutuhan_mu/presentation/kebutuhan_mu_screen.dart';
import '../../features/kebutuhan_mu/presentation/kebutuhan_mu_section_screen.dart';
import '../../features/mengenal_stunting/presentation/mengenal_stunting_content_screen.dart';
import '../../features/mengenal_stunting/presentation/mengenal_stunting_screen.dart';

final _rootNavigatorKey = GlobalKey<NavigatorState>();
final _shellNavigatorHomeKey = GlobalKey<NavigatorState>(debugLabel: 'shellHome');
final _shellNavigatorProfileKey =
    GlobalKey<NavigatorState>(debugLabel: 'shellProfile');

final appRouterProvider = Provider<GoRouter>((ref) {
  return GoRouter(
    navigatorKey: _rootNavigatorKey,
    observers: [ref.read(analyticsRouteObserverProvider)],
    initialLocation: '/splash',
    refreshListenable: _AuthRefreshListenable(ref),
    redirect: (context, state) {
      final location = state.matchedLocation;
      if (location != '/splash') {
        ref.read(analyticsServiceProvider).trackScreenView(location);
      }

      final authState = ref.read(authStateProvider);
      final user = authState.valueOrNull;
      final isSplash = location == '/splash';
      final isAuthRoute = location == '/login' ||
          location == '/register' ||
          location == '/forgot-password' ||
          location == '/reset-password';

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
        path: '/forgot-password',
        builder: (context, state) => const ForgotPasswordScreen(),
      ),
      GoRoute(
        path: '/reset-password',
        builder: (context, state) => ResetPasswordScreen(
          initialEmail: state.extra as String?,
        ),
      ),
      StatefulShellRoute.indexedStack(
        builder: (context, state, navigationShell) {
          return AppShell(navigationShell: navigationShell);
        },
        branches: [
          StatefulShellBranch(
            navigatorKey: _shellNavigatorHomeKey,
            routes: [
              GoRoute(
                path: '/',
                builder: (context, state) => const HomeScreen(),
                routes: [
                  GoRoute(
                    path: 'app-info',
                    builder: (context, state) => const AppInfoScreen(),
                  ),
                  GoRoute(
                    path: 'mengenal-stunting',
                    builder: (context, state) =>
                        const MengenalStuntingScreen(),
                    routes: [
                      GoRoute(
                        path: ':itemSlug',
                        builder: (context, state) =>
                            MengenalStuntingContentScreen(
                          itemSlug: state.pathParameters['itemSlug']!,
                        ),
                      ),
                    ],
                  ),
                  GoRoute(
                    path: 'kebutuhan-mu',
                    builder: (context, state) => const KebutuhanMuScreen(),
                    routes: [
                      GoRoute(
                        path: ':menuSlug',
                        builder: (context, state) => KebutuhanMuMenuScreen(
                          menuSlug: state.pathParameters['menuSlug']!,
                        ),
                        routes: [
                          GoRoute(
                            path: ':sectionSlug',
                            builder: (context, state) =>
                                KebutuhanMuSectionScreen(
                              menuSlug: state.pathParameters['menuSlug']!,
                              sectionSlug:
                                  state.pathParameters['sectionSlug']!,
                            ),
                            routes: [
                              GoRoute(
                                path: 'group/:groupSlug',
                                builder: (context, state) =>
                                    KebutuhanMuGroupScreen(
                                  menuSlug: state.pathParameters['menuSlug']!,
                                  sectionSlug:
                                      state.pathParameters['sectionSlug']!,
                                  groupSlug:
                                      state.pathParameters['groupSlug']!,
                                ),
                                routes: [
                                  GoRoute(
                                    path: ':itemSlug',
                                    builder: (context, state) =>
                                        KebutuhanMuContentScreen(
                                      menuSlug:
                                          state.pathParameters['menuSlug']!,
                                      itemSlug:
                                          state.pathParameters['itemSlug']!,
                                    ),
                                  ),
                                ],
                              ),
                              GoRoute(
                                path: ':itemSlug',
                                builder: (context, state) =>
                                    KebutuhanMuContentScreen(
                                  menuSlug: state.pathParameters['menuSlug']!,
                                  itemSlug: state.pathParameters['itemSlug']!,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
          StatefulShellBranch(
            navigatorKey: _shellNavigatorProfileKey,
            routes: [
              GoRoute(
                path: '/profile',
                builder: (context, state) => const ProfileScreen(),
              ),
            ],
          ),
        ],
      ),
    ],
  );
});

class _AuthRefreshListenable extends ChangeNotifier {
  _AuthRefreshListenable(this._ref) {
    _ref.listen(authStateProvider, (previous, next) => notifyListeners());
  }

  final Ref _ref;
}
