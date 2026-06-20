import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../config/app_config.dart';
import '../storage/token_storage.dart';
import 'api_exception.dart';

final tokenStorageProvider = Provider<TokenStorage>((ref) => TokenStorage());

final dioProvider = Provider<Dio>((ref) {
  final dio = Dio(
    BaseOptions(
      baseUrl: AppConfig.apiBaseUrl,
      connectTimeout: const Duration(seconds: 20),
      receiveTimeout: const Duration(seconds: 20),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ),
  );

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await ref.read(tokenStorageProvider).readToken();
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (error, handler) async {
        final response = error.response;
        if (response?.data is Map<String, dynamic>) {
          final data = response!.data as Map<String, dynamic>;
          handler.reject(
            DioException(
              requestOptions: error.requestOptions,
              response: response,
              error: ApiException(
                data['message']?.toString() ?? 'Terjadi kesalahan pada server.',
                statusCode: response.statusCode,
                errors: data['errors'],
              ),
            ),
          );
          return;
        }

        final retryCount = (error.requestOptions.extra['retryCount'] as int?) ?? 0;
        final shouldRetry = retryCount < 2 &&
            (error.type == DioExceptionType.connectionError ||
                error.type == DioExceptionType.connectionTimeout ||
                error.type == DioExceptionType.receiveTimeout ||
                error.type == DioExceptionType.sendTimeout);

        if (shouldRetry) {
          error.requestOptions.extra['retryCount'] = retryCount + 1;
          await Future<void>.delayed(Duration(milliseconds: 600 * (retryCount + 1)));
          try {
            final retried = await dio.fetch<dynamic>(error.requestOptions);
            handler.resolve(retried);
            return;
          } catch (_) {
            // Lanjut ke error handler default di bawah.
          }
        }

        handler.next(error);
      },
    ),
  );

  return dio;
});

Map<String, dynamic> parseApiData(dynamic responseData) {
  if (responseData is! Map<String, dynamic>) {
    throw ApiException('Format response tidak valid.');
  }
  if (responseData['success'] != true) {
    throw ApiException(
      responseData['message']?.toString() ?? 'Permintaan gagal.',
    );
  }
  return responseData['data'] as Map<String, dynamic>? ?? {};
}

List<dynamic> parseApiList(dynamic responseData) {
  if (responseData is! Map<String, dynamic>) {
    throw ApiException('Format response tidak valid.');
  }
  if (responseData['success'] != true) {
    throw ApiException(
      responseData['message']?.toString() ?? 'Permintaan gagal.',
    );
  }
  final data = responseData['data'];
  if (data is Map<String, dynamic> && data['items'] is List) {
    return data['items'] as List;
  }
  if (data is List) {
    return data;
  }
  return [];
}

String _dioErrorMessage(DioException error) {
  switch (error.type) {
    case DioExceptionType.connectionTimeout:
    case DioExceptionType.receiveTimeout:
    case DioExceptionType.sendTimeout:
      return 'Server tidak merespons. Periksa koneksi internet atau coba lagi.';
    case DioExceptionType.connectionError:
      return 'Tidak dapat terhubung ke server. Periksa URL API dan koneksi jaringan.';
    default:
      return error.message ?? 'Tidak dapat terhubung ke server.';
  }
}

Never rethrowApi(DioException error) {
  if (error.error is ApiException) {
    throw error.error as ApiException;
  }
  if (error.response?.statusCode == 401) {
    throw ApiException('Sesi berakhir. Silakan login kembali.', statusCode: 401);
  }
  throw ApiException(
    _dioErrorMessage(error),
    statusCode: error.response?.statusCode,
  );
}
