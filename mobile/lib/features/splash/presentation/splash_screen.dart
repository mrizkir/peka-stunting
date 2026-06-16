import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../auth/providers/auth_provider.dart';
import '../providers/splash_provider.dart';
import '../models/splash_image_data.dart';
import 'widgets/splash_logo.dart';

/// Layar pembuka sebelum Login atau Home (jika sudah login).
class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  static const Duration minDisplayDuration = Duration(milliseconds: 2500);
  static const Duration maxWaitDuration = Duration(seconds: 10);

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> {
  bool _minTimeElapsed = false;
  bool _hasNavigated = false;
  bool _splashContentReady = false;
  bool _minTimerStarted = false;
  bool _initialSplashChecked = false;
  Timer? _maxWaitTimer;
  Timer? _minDisplayTimer;

  @override
  void initState() {
    super.initState();
    _maxWaitTimer = Timer(SplashScreen.maxWaitDuration, () {
      if (!mounted || _splashContentReady) {
        return;
      }
      _markSplashContentReady();
    });
  }

  @override
  void dispose() {
    _maxWaitTimer?.cancel();
    _minDisplayTimer?.cancel();
    super.dispose();
  }

  void _markSplashContentReady() {
    if (_splashContentReady) {
      return;
    }
    _maxWaitTimer?.cancel();
    setState(() => _splashContentReady = true);
    _startMinDisplayTimer();
  }

  void _startMinDisplayTimer() {
    if (_minTimerStarted) {
      return;
    }
    _minTimerStarted = true;
    _minDisplayTimer = Timer(SplashScreen.minDisplayDuration, () {
      if (!mounted) {
        return;
      }
      setState(() => _minTimeElapsed = true);
      _navigateWhenReady();
    });
  }

  void _handleSplashResolved(SplashImageData data) {
    if (_splashContentReady) {
      return;
    }
    if (!data.hasDisplayableImage) {
      _markSplashContentReady();
    }
  }

  void _navigateWhenReady() {
    if (!_minTimeElapsed || !_splashContentReady || _hasNavigated || !mounted) {
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

    ref.listen(splashImageProvider, (previous, next) {
      next.when(
        data: (data) => _handleSplashResolved(data),
        error: (_, __) => _markSplashContentReady(),
        loading: () {},
      );
    });

    final splashAsync = ref.watch(splashImageProvider);
    if (!_initialSplashChecked && !splashAsync.isLoading) {
      _initialSplashChecked = true;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        splashAsync.when(
          data: _handleSplashResolved,
          error: (_, __) => _markSplashContentReady(),
          loading: () {},
        );
      });
    }

    final splashData = splashAsync.valueOrNull;

    return Scaffold(
      backgroundColor: const Color(0xFFA0C49D),
      body: SplashLogo(
        remoteUrl: splashData?.remoteUrl,
        localPath: splashData?.localPath,
        isLoading: splashAsync.isLoading,
        onImageLoaded: _markSplashContentReady,
        onImageError: _markSplashContentReady,
      ),
    );
  }
}
