import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/analytics/analytics_providers.dart';
import '../../../core/analytics/analytics_service.dart';
import '../../../core/network/api_client.dart';
import '../models/bmi_screening_submission.dart';

final bmiScreeningRepositoryProvider = Provider<BmiScreeningRepository>((ref) {
  return BmiScreeningRepository(
    ref.read(dioProvider),
    ref.read(analyticsServiceProvider),
  );
});

class BmiScreeningRepository {
  BmiScreeningRepository(this._dio, this._analytics);

  final Dio _dio;
  final AnalyticsService _analytics;

  Future<BmiScreeningSubmission> submit({
    required String menuSlug,
    required double weightKg,
    required double heightCm,
  }) async {
    try {
      final response = await _dio.post(
        '/screening-submissions/cek-imt',
        data: {
          'menu_slug': menuSlug,
          'weight_kg': weightKg,
          'height_cm': heightCm,
        },
      );
      final data = parseApiData(response.data);
      final submission = BmiScreeningSubmission.fromJson(data);
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
