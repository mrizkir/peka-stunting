import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../models/child_models.dart';

final childrenRepositoryProvider = Provider<ChildrenRepository>((ref) {
  return ChildrenRepository(ref.read(dioProvider));
});

class ChildrenRepository {
  ChildrenRepository(this._dio);

  final Dio _dio;

  Future<List<ChildSummary>> fetchChildren({String? query}) async {
    try {
      final response = await _dio.get(
        '/children',
        queryParameters: {
          if (query != null && query.isNotEmpty) 'q': query,
        },
      );
      final list = parseApiList(response.data);
      return list
          .map((item) => ChildSummary.fromJson(item as Map<String, dynamic>))
          .toList();
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<ChildDetail> fetchChild(int id) async {
    try {
      final response = await _dio.get('/children/$id');
      final data = parseApiData(response.data);
      return ChildDetail.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<ChildDetail> createChild(Map<String, dynamic> payload) async {
    try {
      final response = await _dio.post('/children', data: payload);
      final data = parseApiData(response.data);
      return ChildDetail.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<MeasurementSummary> addMeasurement({
    required int childId,
    required Map<String, dynamic> payload,
  }) async {
    try {
      final response = await _dio.post(
        '/children/$childId/measurements',
        data: payload,
      );
      final data = parseApiData(response.data);
      return MeasurementSummary.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<RiskSummary> assessRisk({
    required int childId,
    int? measurementId,
  }) async {
    try {
      final response = await _dio.post(
        '/children/$childId/risk-assessments',
        data: {
          'measurement_id': ?measurementId,
        },
      );
      final data = parseApiData(response.data);
      return RiskSummary.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
