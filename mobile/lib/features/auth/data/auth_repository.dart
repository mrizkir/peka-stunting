import 'package:dio/dio.dart';
import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/analytics/analytics_event_names.dart';
import '../../../core/analytics/analytics_providers.dart';
import '../../../core/analytics/analytics_service.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/storage/token_storage.dart';
import '../models/user_model.dart';

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    ref.read(dioProvider),
    ref.read(tokenStorageProvider),
    ref.read(analyticsServiceProvider),
  );
});

class AuthRepository {
  AuthRepository(this._dio, this._tokenStorage, this._analytics);

  final Dio _dio;
  final TokenStorage _tokenStorage;
  final AnalyticsService _analytics;

  Future<UserModel> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String passwordConfirmation,
    String? gender,
    String? birthDate,
  }) async {
    try {
      final response = await _dio.post(
        '/auth/register',
        data: {
          'name': name,
          'email': email,
          'phone': phone,
          'password': password,
          'password_confirmation': passwordConfirmation,
          if (gender != null && gender.isNotEmpty) 'gender': gender,
          if (birthDate != null && birthDate.isNotEmpty) 'birth_date': birthDate,
          'device_name': 'android',
        },
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw ApiException(data['message']?.toString() ?? 'Registrasi gagal.');
      }
      final payload = data['data'] as Map<String, dynamic>;
      final token = payload['token'] as String;
      await _tokenStorage.saveToken(token);
      final user = UserModel.fromJson(payload['user'] as Map<String, dynamic>);
      await _persistUser(user);
      await _analytics.setUserId('${user.id}');
      await _analytics.track(AnalyticsEventNames.registerSuccess);
      unawaited(_analytics.flush());
      return user;
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<UserModel> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await _dio.post(
        '/auth/login',
        data: {
          'email': email,
          'password': password,
          'device_name': 'android',
        },
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw ApiException(data['message']?.toString() ?? 'Login gagal.');
      }
      final payload = data['data'] as Map<String, dynamic>;
      final token = payload['token'] as String;
      await _tokenStorage.saveToken(token);
      final user = UserModel.fromJson(payload['user'] as Map<String, dynamic>);
      await _persistUser(user);
      await _analytics.setUserId('${user.id}');
      await _analytics.track(AnalyticsEventNames.loginSuccess);
      unawaited(_analytics.flush());
      return user;
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<void> forgotPassword({required String email}) async {
    try {
      final response = await _dio.post(
        '/auth/forgot-password',
        data: {'email': email},
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw ApiException(
          data['message']?.toString() ?? 'Permintaan reset password gagal.',
        );
      }
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<void> resetPassword({
    required String email,
    required String token,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _dio.post(
        '/auth/reset-password',
        data: {
          'email': email,
          'token': token,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw ApiException(
          data['message']?.toString() ?? 'Reset password gagal.',
        );
      }
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<UserModel> me() async {
    try {
      final response = await _dio.get('/auth/me');
      final data = parseApiData(response.data);
      final user = UserModel.fromJson(data);
      await _persistUser(user);
      return user;
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<UserModel?> cachedUser() async {
    final raw = await _tokenStorage.readUserJson();
    if (raw == null) {
      return null;
    }

    return UserModel.fromJson(raw);
  }

  Future<void> clearLocalSession() async {
    await _tokenStorage.clearToken();
    await _analytics.setUserId(null);
  }

  Future<void> logout() async {
    try {
      await _dio.post('/auth/logout');
    } catch (_) {
      // Abaikan error logout jaringan, token tetap dihapus lokal.
    } finally {
      await _tokenStorage.clearToken();
      await _analytics.setUserId(null);
      await _analytics.track(AnalyticsEventNames.logout);
      unawaited(_analytics.flush());
    }
  }

  Future<void> deleteAccount() async {
    try {
      await _dio.delete('/auth/account');
    } on DioException catch (error) {
      rethrowApi(error);
    } finally {
      await _tokenStorage.clearToken();
      await _analytics.setUserId(null);
    }
  }

  Future<String?> currentToken() => _tokenStorage.readToken();

  Future<UserModel> uploadProfilePhoto(String filePath) async {
    try {
      final response = await _dio.post(
        '/auth/profile-photo',
        data: FormData.fromMap({
          'profile_photo': await MultipartFile.fromFile(filePath),
        }),
      );
      final data = parseApiData(response.data);
      final user = UserModel.fromJson(data);
      await _persistUser(user);
      return user;
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<UserModel> deleteProfilePhoto() async {
    try {
      final response = await _dio.delete('/auth/profile-photo');
      final data = parseApiData(response.data);
      final user = UserModel.fromJson(data);
      await _persistUser(user);
      return user;
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<void> _persistUser(UserModel user) async {
    await _tokenStorage.saveUserJson({
      'id': user.id,
      'name': user.name,
      'email': user.email,
      'phone': user.phone,
      'roles': user.roles,
      'profile_photo_url': user.profilePhotoUrl,
    });
  }
}
