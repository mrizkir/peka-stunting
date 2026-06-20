import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/analytics/analytics_providers.dart';
import '../../../core/analytics/analytics_service.dart';
import '../../../core/network/api_client.dart';
import '../models/lila_screening_submission.dart';

final lilaScreeningRepositoryProvider =
    Provider<LilaScreeningRepository>((ref) {
  return LilaScreeningRepository(
    ref.read(dioProvider),
    ref.read(analyticsServiceProvider),
  );
});

class LilaScreeningRepository {
  LilaScreeningRepository(this._dio, this._analytics);

  final Dio _dio;
  final AnalyticsService _analytics;

  Future<LilaScreeningSubmission> submit({
    required String menuSlug,
    required int ageYears,
    required double lilaCm,
  }) async {
    try {
      final response = await _dio.post(
        '/screening-submissions/cek-lila',
        data: {
          'menu_slug': menuSlug,
          'age_years': ageYears,
          'lila_cm': lilaCm,
        },
      );
      final data = parseApiData(response.data);
      final submission = LilaScreeningSubmission.fromJson(data);
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
