import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../network/api_client.dart';
import 'analytics_queue.dart';
import 'analytics_service.dart';

final analyticsQueueProvider = Provider<AnalyticsQueue>((ref) {
  return AnalyticsQueue();
});

final analyticsServiceProvider = Provider<AnalyticsService>((ref) {
  return AnalyticsService(
    ref.read(dioProvider),
    ref.read(analyticsQueueProvider),
  );
});

final analyticsRouteObserverProvider = Provider<AnalyticsRouteObserver>((ref) {
  return AnalyticsRouteObserver(ref);
});

class AnalyticsRouteObserver extends NavigatorObserver {
  AnalyticsRouteObserver(this._ref);

  final Ref _ref;

  @override
  void didPush(Route<dynamic> route, Route<dynamic>? previousRoute) {
    super.didPush(route, previousRoute);
    _trackRoute(route);
  }

  @override
  void didReplace({Route<dynamic>? newRoute, Route<dynamic>? oldRoute}) {
    super.didReplace(newRoute: newRoute, oldRoute: oldRoute);
    if (newRoute != null) {
      _trackRoute(newRoute);
    }
  }

  @override
  void didPop(Route<dynamic> route, Route<dynamic>? previousRoute) {
    super.didPop(route, previousRoute);
    if (previousRoute != null) {
      _trackRoute(previousRoute);
    }
  }

  void _trackRoute(Route<dynamic> route) {
    final location = route.settings.name;
    if (location == null || location.isEmpty) {
      return;
    }

    _ref.read(analyticsServiceProvider).trackScreenView(location);
  }
}
