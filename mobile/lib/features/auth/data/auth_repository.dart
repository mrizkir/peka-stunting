import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/storage/token_storage.dart';
import '../models/user_model.dart';

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    ref.read(dioProvider),
    ref.read(tokenStorageProvider),
  );
});

class AuthRepository {
  AuthRepository(this._dio, this._tokenStorage);

  final Dio _dio;
  final TokenStorage _tokenStorage;

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
      return UserModel.fromJson(payload['user'] as Map<String, dynamic>);
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
      return UserModel.fromJson(payload['user'] as Map<String, dynamic>);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<UserModel> me() async {
    try {
      final response = await _dio.get('/auth/me');
      final data = parseApiData(response.data);
      return UserModel.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post('/auth/logout');
    } catch (_) {
      // Abaikan error logout jaringan, token tetap dihapus lokal.
    } finally {
      await _tokenStorage.clearToken();
    }
  }

  Future<void> deleteAccount() async {
    try {
      await _dio.delete('/auth/account');
    } on DioException catch (error) {
      rethrowApi(error);
    } finally {
      await _tokenStorage.clearToken();
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
      return UserModel.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<UserModel> deleteProfilePhoto() async {
    try {
      final response = await _dio.delete('/auth/profile-photo');
      final data = parseApiData(response.data);
      return UserModel.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }
}
