import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../models/breastfeeding_screening_submission.dart';

final breastfeedingScreeningRepositoryProvider =
    Provider<BreastfeedingScreeningRepository>((ref) {
  return BreastfeedingScreeningRepository(ref.read(dioProvider));
});

class BreastfeedingScreeningRepository {
  BreastfeedingScreeningRepository(this._dio);

  final Dio _dio;

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
      return BreastfeedingScreeningSubmission.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
