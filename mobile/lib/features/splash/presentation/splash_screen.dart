import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../auth/providers/auth_provider.dart';
import '../providers/splash_provider.dart';
import 'widgets/splash_logo.dart';

/// Layar pembuka sebelum Login atau Home (jika sudah login).
class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  static const Duration minDisplayDuration = Duration(milliseconds: 2500);

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> {
  bool _minTimeElapsed = false;
  bool _hasNavigated = false;

  @override
  void initState() {
    super.initState();
    Future<void>.delayed(SplashScreen.minDisplayDuration, () {
      if (!mounted) {
        return;
      }
      setState(() => _minTimeElapsed = true);
      _navigateWhenReady();
    });
  }

  void _navigateWhenReady() {
    if (!_minTimeElapsed || _hasNavigated || !mounted) {
      return;
    }

    final auth = ref.read(authStateProvider);
    if (auth.isLoading) {
      return;
    }

    _hasNavigated = true;
    final user = auth.valueOrNull;
    context.go(user == null ? '/login' : '/');
  }

  @override
  Widget build(BuildContext context) {
    ref.listen(authStateProvider, (previous, next) {
      if (!next.isLoading) {
        _navigateWhenReady();
      }
    });

    final splashImageUrl = ref.watch(splashImageUrlProvider).valueOrNull;

    return Scaffold(
      body: SplashLogo(remoteUrl: splashImageUrl),
    );
  }
}
