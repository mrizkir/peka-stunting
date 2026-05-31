import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../models/bmi_screening_submission.dart';

final bmiScreeningRepositoryProvider = Provider<BmiScreeningRepository>((ref) {
  return BmiScreeningRepository(ref.read(dioProvider));
});

class BmiScreeningRepository {
  BmiScreeningRepository(this._dio);

  final Dio _dio;

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
      return BmiScreeningSubmission.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
