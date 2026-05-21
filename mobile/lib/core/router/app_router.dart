import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/presentation/login_screen.dart';
import '../../features/splash/presentation/splash_screen.dart';
import '../../features/auth/presentation/register_screen.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../features/children/presentation/add_child_screen.dart';
import '../../features/children/presentation/add_measurement_screen.dart';
import '../../features/children/presentation/child_detail_screen.dart';
import '../../features/children/presentation/children_list_screen.dart';
import '../../features/education/presentation/education_content_screen.dart';
import '../../features/education/presentation/education_menu_screen.dart';
import '../../features/education/presentation/education_menus_screen.dart';
import '../../features/home/presentation/home_screen.dart';

final _rootNavigatorKey = GlobalKey<NavigatorState>();

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authStateProvider);

  return GoRouter(
    navigatorKey: _rootNavigatorKey,
    initialLocation: '/splash',
    refreshListenable: _AuthRefreshListenable(ref),
    redirect: (context, state) {
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
        path: '/education',
        builder: (context, state) => const EducationMenusScreen(),
      ),
      GoRoute(
        path: '/education/:menuSlug',
        builder: (context, state) => EducationMenuScreen(
          menuSlug: state.pathParameters['menuSlug']!,
        ),
      ),
      GoRoute(
        path: '/education/:menuSlug/:itemSlug',
        builder: (context, state) => EducationContentScreen(
          menuSlug: state.pathParameters['menuSlug']!,
          itemSlug: state.pathParameters['itemSlug']!,
        ),
      ),
      GoRoute(
        path: '/children',
        builder: (context, state) => const ChildrenListScreen(),
      ),
      GoRoute(
        path: '/children/new',
        builder: (context, state) => const AddChildScreen(),
      ),
      GoRoute(
        path: '/children/:id',
        builder: (context, state) => ChildDetailScreen(
          childId: int.parse(state.pathParameters['id']!),
        ),
      ),
      GoRoute(
        path: '/children/:id/measurement',
        builder: (context, state) => AddMeasurementScreen(
          childId: int.parse(state.pathParameters['id']!),
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
