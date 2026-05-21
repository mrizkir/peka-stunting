import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../mengenal_stunting_config.dart';
import '../models/mengenal_stunting_models.dart';

final mengenalStuntingRepositoryProvider =
    Provider<MengenalStuntingRepository>((ref) {
  return MengenalStuntingRepository(ref.read(dioProvider));
});

class MengenalStuntingRepository {
  MengenalStuntingRepository(this._dio);

  final Dio _dio;

  Future<MengenalStuntingMenu> fetchMenu() async {
    try {
      final response = await _dio.get(
        '/education/menus/${MengenalStuntingConfig.menuSlug}',
      );
      final data = parseApiData(response.data);
      return MengenalStuntingMenu.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<MengenalStuntingContent> fetchContent(String itemSlug) async {
    try {
      final response = await _dio.get(
        '/education/menus/${MengenalStuntingConfig.menuSlug}/contents/$itemSlug',
      );
      final data = parseApiData(response.data);
      return MengenalStuntingContent.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
