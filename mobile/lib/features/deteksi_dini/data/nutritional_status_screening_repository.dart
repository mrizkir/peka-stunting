import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../core/network/api_client.dart';
import '../models/nutritional_status_screening_submission.dart';
import '../domain/nutritional_status_calculator.dart';

final nutritionalStatusScreeningRepositoryProvider =
    Provider<NutritionalStatusScreeningRepository>((ref) {
  return NutritionalStatusScreeningRepository(ref.read(dioProvider));
});

class NutritionalStatusScreeningRepository {
  NutritionalStatusScreeningRepository(this._dio);

  final Dio _dio;
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
      return NutritionalStatusScreeningSubmission.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
