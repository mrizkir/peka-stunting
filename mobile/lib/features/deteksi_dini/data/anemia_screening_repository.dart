import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../models/anemia_screening_submission.dart';

final anemiaScreeningRepositoryProvider =
    Provider<AnemiaScreeningRepository>((ref) {
  return AnemiaScreeningRepository(ref.read(dioProvider));
});

class AnemiaScreeningRepository {
  AnemiaScreeningRepository(this._dio);

  final Dio _dio;

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
      return AnemiaScreeningSubmission.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
