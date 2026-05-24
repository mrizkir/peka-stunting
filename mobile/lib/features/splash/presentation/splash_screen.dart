import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/config/app_config.dart';
import '../../../core/theme/app_theme.dart';
import '../../auth/providers/auth_provider.dart';
import '../providers/splash_provider.dart';

/// Layar pembuka sebelum Login atau Home (jika sudah login).
class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  static const Duration minDisplayDuration = Duration(milliseconds: 2500);

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> {
  bool _minTimeElapsed = false;
  bool _splashResolved = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      final splash = ref.read(splashImageUrlProvider);
      if (splash.hasValue || splash.hasError) {
        _markSplashResolved();
      }
    });
    Future<void>.delayed(SplashScreen.minDisplayDuration, () {
      if (! mounted) {
        return;
      }
      setState(() => _minTimeElapsed = true);
      _navigateWhenReady();
    });
  }

  void _markSplashResolved() {
    if (_splashResolved || ! mounted) {
      return;
    }
    setState(() => _splashResolved = true);
    _navigateWhenReady();
  }

  void _navigateWhenReady() {
    if (! _minTimeElapsed || ! _splashResolved || ! mounted) {
      return;
    }

    final auth = ref.read(authStateProvider);
    if (auth.isLoading) {
      return;
    }

    final user = auth.valueOrNull;
    context.go(user == null ? '/login' : '/');
  }

  @override
  Widget build(BuildContext context) {
    ref.listen(authStateProvider, (previous, next) {
      if (! next.isLoading) {
        _navigateWhenReady();
      }
    });

    ref.listen(splashImageUrlProvider, (previous, next) {
      next.when(
        data: (_) => _markSplashResolved(),
        error: (_, __) => _markSplashResolved(),
        loading: () {},
      );
    });

    return Scaffold(
      backgroundColor: AppTheme.primary,
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Image.asset(
                  AppConfig.logoAssetPath,
                  height: 96,
                  fit: BoxFit.contain,
                ),
                const SizedBox(height: 12),
                Text(
                  AppConfig.appTagline,
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: Colors.white.withValues(alpha: 0.92),
                        height: 1.45,
                        fontWeight: FontWeight.w500,
                      ),
                ),
                const SizedBox(height: 48),
                const SizedBox(
                  height: 28,
                  width: 28,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.5,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
