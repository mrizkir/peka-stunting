import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../app_info_config.dart';
import '../models/app_info_models.dart';

final appInfoRepositoryProvider = Provider<AppInfoRepository>((ref) {
  return AppInfoRepository(ref.read(dioProvider));
});

class AppInfoRepository {
  AppInfoRepository(this._dio);

  final Dio _dio;

  Future<AppInfoContent> fetchContent() async {
    try {
      final response = await _dio.get(
        '/education/menus/${AppInfoConfig.menuSlug}/contents/${AppInfoConfig.itemSlug}',
      );
      final data = parseApiData(response.data);
      return AppInfoContent.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
