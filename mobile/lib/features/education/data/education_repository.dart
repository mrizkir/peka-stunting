import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../models/education_models.dart';

final educationRepositoryProvider = Provider<EducationRepository>((ref) {
  return EducationRepository(ref.read(dioProvider));
});

class EducationRepository {
  EducationRepository(this._dio);

  final Dio _dio;

  Future<List<EducationMenu>> fetchMenus() async {
    try {
      final response = await _dio.get('/education/menus');
      final list = parseApiList(response.data);
      return list
          .map((item) => EducationMenu.fromJson(item as Map<String, dynamic>))
          .toList();
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<EducationMenuDetail> fetchMenuDetail(String menuSlug) async {
    try {
      final response = await _dio.get('/education/menus/$menuSlug');
      final data = parseApiData(response.data);
      return EducationMenuDetail.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<EducationContentDetail> fetchContent({
    required String menuSlug,
    required String itemSlug,
  }) async {
    try {
      final response = await _dio.get(
        '/education/menus/$menuSlug/contents/$itemSlug',
      );
      final data = parseApiData(response.data);
      return EducationContentDetail.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
