import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/analytics/analytics_providers.dart';
import '../../../core/analytics/analytics_service.dart';
import '../../../core/network/api_client.dart';
import '../models/breastfeeding_screening_submission.dart';

final breastfeedingScreeningRepositoryProvider =
    Provider<BreastfeedingScreeningRepository>((ref) {
  return BreastfeedingScreeningRepository(
    ref.read(dioProvider),
    ref.read(analyticsServiceProvider),
  );
});

class BreastfeedingScreeningRepository {
  BreastfeedingScreeningRepository(this._dio, this._analytics);

  final Dio _dio;
  final AnalyticsService _analytics;

  Future<BreastfeedingScreeningSubmission> submit({
    required String menuSlug,
    required Map<String, bool> answers,
  }) async {
    try {
      final response = await _dio.post(
        '/screening-submissions/cek-keberhasilan-menyusui',
        data: {
          'menu_slug': menuSlug,
          'answers': answers,
        },
      );
      final data = parseApiData(response.data);
      final submission = BreastfeedingScreeningSubmission.fromJson(data);
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
