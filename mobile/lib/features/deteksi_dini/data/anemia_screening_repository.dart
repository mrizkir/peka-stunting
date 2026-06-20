import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/analytics/analytics_providers.dart';
import '../../../core/analytics/analytics_service.dart';
import '../../../core/network/api_client.dart';
import '../models/anemia_screening_submission.dart';

final anemiaScreeningRepositoryProvider =
    Provider<AnemiaScreeningRepository>((ref) {
  return AnemiaScreeningRepository(
    ref.read(dioProvider),
    ref.read(analyticsServiceProvider),
  );
});

class AnemiaScreeningRepository {
  AnemiaScreeningRepository(this._dio, this._analytics);

  final Dio _dio;
  final AnalyticsService _analytics;

  Future<AnemiaScreeningSubmission> submit({
    required String menuSlug,
    required Map<String, bool> answers,
  }) async {
    try {
      final response = await _dio.post(
        '/screening-submissions/cek-risiko-anemia',
        data: {
          'menu_slug': menuSlug,
          'answers': answers,
        },
      );
      final data = parseApiData(response.data);
      final submission = AnemiaScreeningSubmission.fromJson(data);
      unawaited(
        _analytics.trackScreeningCompleted(
          calculatorSlug: submission.calculatorSlug,
          menuSlug: submission.menuSlug,
          category: submission.category,
        ),
      );
      unawaited(_analytics.flush());
      return submission;
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
