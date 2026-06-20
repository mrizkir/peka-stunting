import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'analytics_providers.dart';

class AppLifecycleObserver extends ConsumerStatefulWidget {
  const AppLifecycleObserver({
    super.key,
    required this.child,
  });

  final Widget child;

  @override
  ConsumerState<AppLifecycleObserver> createState() =>
      _AppLifecycleObserverState();
}

class _AppLifecycleObserverState extends ConsumerState<AppLifecycleObserver>
    with WidgetsBindingObserver {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    Future.microtask(() async {
      await ref.read(analyticsServiceProvider).initialize();
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final analytics = ref.read(analyticsServiceProvider);

    switch (state) {
      case AppLifecycleState.resumed:
        unawaited(analytics.startNewSession());
        unawaited(analytics.flush());
      case AppLifecycleState.paused:
      case AppLifecycleState.detached:
        unawaited(analytics.endSession());
      case AppLifecycleState.inactive:
      case AppLifecycleState.hidden:
        break;
    }
  }

  @override
  Widget build(BuildContext context) => widget.child;
}
