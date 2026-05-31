import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../models/lila_screening_submission.dart';

final lilaScreeningRepositoryProvider =
    Provider<LilaScreeningRepository>((ref) {
  return LilaScreeningRepository(ref.read(dioProvider));
});

class LilaScreeningRepository {
  LilaScreeningRepository(this._dio);

  final Dio _dio;

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
      return LilaScreeningSubmission.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
