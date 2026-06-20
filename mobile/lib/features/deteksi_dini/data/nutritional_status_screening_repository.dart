import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../core/analytics/analytics_providers.dart';
import '../../../core/analytics/analytics_service.dart';
import '../../../core/network/api_client.dart';
import '../domain/nutritional_status_calculator.dart';
import '../models/nutritional_status_screening_submission.dart';

final nutritionalStatusScreeningRepositoryProvider =
    Provider<NutritionalStatusScreeningRepository>((ref) {
  return NutritionalStatusScreeningRepository(
    ref.read(dioProvider),
    ref.read(analyticsServiceProvider),
  );
});

class NutritionalStatusScreeningRepository {
  NutritionalStatusScreeningRepository(this._dio, this._analytics);

  final Dio _dio;
  final AnalyticsService _analytics;
  static final _dateFormat = DateFormat('yyyy-MM-dd');

  Future<NutritionalStatusScreeningSubmission> submit({
    required String menuSlug,
    required DateTime birthDate,
    required String gender,
    required double weightKg,
    required double heightCm,
  }) async {
    try {
      final response = await _dio.post(
        '/screening-submissions/periksa-status-gizi',
        data: {
          'menu_slug': menuSlug,
          'birth_date': _dateFormat.format(birthDate),
          'gender': gender,
          'age_months': NutritionalStatusCalculator.ageInMonths(birthDate),
          'weight_kg': weightKg,
          'height_cm': heightCm,
        },
      );
      final data = parseApiData(response.data);
      final submission = NutritionalStatusScreeningSubmission.fromJson(data);
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
