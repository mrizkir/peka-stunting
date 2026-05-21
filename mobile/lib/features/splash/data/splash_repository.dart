import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';

final splashRepositoryProvider = Provider<SplashRepository>((ref) {
  return SplashRepository(ref.read(dioProvider));
});

class SplashRepository {
  SplashRepository(this._dio);

  final Dio _dio;

  /// URL logo dari backend; `null` jika tidak ada atau gagal diambil.
  Future<String?> fetchSplashImageUrl() async {
    try {
      final response = await _dio.get('/app/splash');
      final data = parseApiData(response.data);
      final url = data['image_url']?.toString().trim();

      if (url == null || url.isEmpty) {
        return null;
      }

      return url;
    } on DioException {
      return null;
    } catch (_) {
      return null;
    }
  }
}
