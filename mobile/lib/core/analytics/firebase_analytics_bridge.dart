import 'package:firebase_analytics/firebase_analytics.dart';

class FirebaseAnalyticsBridge {
  FirebaseAnalyticsBridge._();

  static FirebaseAnalytics? _analytics;

  static Future<void> initialize() async {
    _analytics = FirebaseAnalytics.instance;
  }

  static Future<void> logEvent({
    required String name,
    Map<String, Object?>? parameters,
  }) async {
    final analytics = _analytics;
    if (analytics == null) {
      return;
    }

    final sanitized = parameters?.map(
      (key, value) => MapEntry(key, value ?? ''),
    );

    await analytics.logEvent(
      name: name,
      parameters: sanitized,
    );
  }

  static Future<void> setUserId(String? userId) async {
    final analytics = _analytics;
    if (analytics == null) {
      return;
    }

    await analytics.setUserId(id: userId);
  }
}
