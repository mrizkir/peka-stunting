import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../kebutuhan_mu_config.dart';
import '../models/kebutuhan_mu_models.dart';

final kebutuhanMuRepositoryProvider = Provider<KebutuhanMuRepository>((ref) {
  return KebutuhanMuRepository(ref.read(dioProvider));
});

class KebutuhanMuRepository {
  KebutuhanMuRepository(this._dio);

  final Dio _dio;

  Future<List<KebutuhanMuMenuSummary>> fetchTargetGroups() async {
    try {
      final response = await _dio.get('/education/menus');
      final list = parseApiList(response.data);
      return list
          .map(
            (item) =>
                KebutuhanMuMenuSummary.fromJson(item as Map<String, dynamic>),
          )
          .where((menu) => menu.slug != KebutuhanMuConfig.excludedMenuSlug)
          .toList();
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<KebutuhanMuMenuDetail> fetchMenuDetail(String menuSlug) async {
    try {
      final response = await _dio.get('/education/menus/$menuSlug');
      final data = parseApiData(response.data);
      return KebutuhanMuMenuDetail.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<KebutuhanMuContent> fetchContent({
    required String menuSlug,
    required String itemSlug,
  }) async {
    try {
      final response = await _dio.get(
        '/education/menus/$menuSlug/contents/$itemSlug',
      );
      final data = parseApiData(response.data);
      return KebutuhanMuContent.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
