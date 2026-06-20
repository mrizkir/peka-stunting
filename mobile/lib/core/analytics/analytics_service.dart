import 'dart:async';
import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:uuid/uuid.dart';

import 'analytics_event_names.dart';
import 'analytics_queue.dart';
import 'firebase_analytics_bridge.dart';

class AnalyticsService {
  AnalyticsService(this._dio, this._queue);

  final Dio _dio;
  final AnalyticsQueue _queue;
  final _uuid = const Uuid();

  String _sessionId = '';
  DateTime? _sessionStartedAt;
  String? _appVersion;
  String? _lastScreenRoute;
  bool _initialized = false;
  bool _isFlushing = false;

  String get sessionId => _sessionId;

  Future<void> initialize() async {
    if (_initialized) {
      return;
    }

    _initialized = true;
    _sessionId = _uuid.v4();
    _sessionStartedAt = DateTime.now();

    try {
      final packageInfo = await PackageInfo.fromPlatform();
      _appVersion = packageInfo.version;
    } catch (_) {
      _appVersion = null;
    }

    await FirebaseAnalyticsBridge.initialize();
    await track(AnalyticsEventNames.appOpen);
    await track(AnalyticsEventNames.sessionStart);
    unawaited(flush());
  }

  Future<void> setUserId(String? userId) async {
    await FirebaseAnalyticsBridge.setUserId(userId);
  }

  Future<void> track(
    String eventName, {
    Map<String, String>? properties,
  }) async {
    if (_sessionId.isEmpty) {
      _sessionId = _uuid.v4();
    }

    await _queue.enqueueEvent(
      eventName: eventName,
      sessionId: _sessionId,
      occurredAt: DateTime.now(),
      properties: properties,
    );

    await FirebaseAnalyticsBridge.logEvent(
      name: eventName,
      parameters: properties,
    );
  }

  Future<void> trackScreenView(String route) {
    if (route.isEmpty || route == _lastScreenRoute) {
      return Future.value();
    }

    _lastScreenRoute = route;

    final properties = <String, String>{'route': route};
    final segments = route.split('/').where((part) => part.isNotEmpty).toList();

    if (segments.contains('kebutuhan-mu') || segments.contains('mengenal-stunting')) {
      final menuIndex = segments.indexOf('kebutuhan-mu');
      final mengenalIndex = segments.indexOf('mengenal-stunting');

      if (menuIndex != -1 && segments.length > menuIndex + 1) {
        properties['menu_slug'] = segments[menuIndex + 1];
      }

      if (mengenalIndex != -1 && segments.length > mengenalIndex + 1) {
        properties['item_slug'] = segments[mengenalIndex + 1];
      }

      final lastSegment = segments.isNotEmpty ? segments.last : null;
      if (lastSegment != null &&
          lastSegment != 'kebutuhan-mu' &&
          lastSegment != 'mengenal-stunting' &&
          !properties.containsKey('item_slug')) {
        properties['item_slug'] = lastSegment;
      }

      if (properties.containsKey('item_slug')) {
        return track(
          AnalyticsEventNames.educationContentView,
          properties: properties,
        );
      }
    }

    return track(AnalyticsEventNames.screenView, properties: properties);
  }

  Future<void> trackScreeningCompleted({
    required String calculatorSlug,
    required String menuSlug,
    String? category,
  }) {
    return track(
      AnalyticsEventNames.screeningCompleted,
      properties: {
        'calculator_slug': calculatorSlug,
        'menu_slug': menuSlug,
        if (category != null) 'category': category,
      },
    );
  }

  Future<void> endSession() async {
    final startedAt = _sessionStartedAt;
    if (startedAt == null || _sessionId.isEmpty) {
      return;
    }

    final endedAt = DateTime.now();
    final durationSeconds = endedAt.difference(startedAt).inSeconds;
    if (durationSeconds < 5) {
      return;
    }

    await track(
      AnalyticsEventNames.sessionEnd,
      properties: {'duration_seconds': '$durationSeconds'},
    );

    await _queue.enqueueSession(
      sessionId: _sessionId,
      startedAt: startedAt,
      endedAt: endedAt,
      durationSeconds: durationSeconds,
    );

    _sessionStartedAt = null;
    unawaited(flush());
  }

  Future<void> startNewSession() async {
    _sessionId = _uuid.v4();
    _sessionStartedAt = DateTime.now();
    _lastScreenRoute = null;
    await track(AnalyticsEventNames.sessionStart);
  }

  Future<void> flush() async {
    if (_isFlushing) {
      return;
    }

    _isFlushing = true;

    try {
      await _flushEvents();
      await _flushSessions();
    } catch (error, stackTrace) {
      debugPrint('Analytics flush failed: $error\n$stackTrace');
    } finally {
      _isFlushing = false;
    }
  }

  Future<void> _flushEvents() async {
    final pending = await _queue.peekEvents();
    if (pending.isEmpty) {
      return;
    }

    await _dio.post(
      '/analytics/events',
      data: {
        'platform': Platform.isIOS ? 'ios' : 'android',
        if (_appVersion != null) 'app_version': _appVersion,
        'events': pending.map((event) => event.toPayload()).toList(),
      },
    );

    await _queue.deleteEvents(pending.map((event) => event.id));
  }

  Future<void> _flushSessions() async {
    final pending = await _queue.peekSessions();
    if (pending.isEmpty) {
      return;
    }

    for (final session in pending) {
      await _dio.post(
        '/analytics/sessions',
        data: {
          'platform': Platform.isIOS ? 'ios' : 'android',
          if (_appVersion != null) 'app_version': _appVersion,
          ...session.toPayload(),
        },
      );
    }

    await _queue.deleteSessions(pending.map((session) => session.id));
  }
}
