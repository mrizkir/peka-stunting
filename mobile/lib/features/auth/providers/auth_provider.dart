import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/auth_repository.dart';
import '../models/user_model.dart';
import '../../../core/analytics/analytics_providers.dart';
import '../../../core/network/api_exception.dart';

final authStateProvider =
    AsyncNotifierProvider<AuthNotifier, UserModel?>(AuthNotifier.new);

class AuthNotifier extends AsyncNotifier<UserModel?> {
  @override
  Future<UserModel?> build() async {
    final repository = ref.read(authRepositoryProvider);
    final token = await repository.currentToken();
    if (token == null || token.isEmpty) {
      return null;
    }

    final cachedUser = await repository.cachedUser();

    try {
      final user = await repository.me();
      await ref.read(analyticsServiceProvider).setUserId('${user.id}');
      return user;
    } on ApiException catch (error) {
      if (error.statusCode == 401) {
        await repository.clearLocalSession();
        return null;
      }
      if (cachedUser != null) {
        return cachedUser;
      }
      rethrow;
    } catch (_) {
      if (cachedUser != null) {
        return cachedUser;
      }
      rethrow;
    }
  }

  Future<void> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String passwordConfirmation,
    String? gender,
    String? birthDate,
  }) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      return ref.read(authRepositoryProvider).register(
            name: name,
            email: email,
            phone: phone,
            password: password,
            passwordConfirmation: passwordConfirmation,
            gender: gender,
            birthDate: birthDate,
          );
    });
    if (state.hasError) {
      throw state.error!;
    }
  }

  Future<void> login(String email, String password) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      return ref.read(authRepositoryProvider).login(
            email: email,
            password: password,
          );
    });
    if (state.hasError) {
      throw state.error!;
    }
  }

  Future<void> logout() async {
    await ref.read(authRepositoryProvider).logout();
    state = const AsyncData(null);
  }

  Future<void> deleteAccount() async {
    await ref.read(authRepositoryProvider).deleteAccount();
    state = const AsyncData(null);
  }

  Future<void> uploadProfilePhoto(String filePath) async {
    final user = await ref.read(authRepositoryProvider).uploadProfilePhoto(filePath);
    state = AsyncData(user);
  }

  Future<void> deleteProfilePhoto() async {
    final user = await ref.read(authRepositoryProvider).deleteProfilePhoto();
    state = AsyncData(user);
  }

  /// Muat ulang profil dari server tanpa menampilkan loading penuh.
  Future<void> refreshUser() async {
    final token = await ref.read(authRepositoryProvider).currentToken();
    if (token == null || token.isEmpty) {
      return;
    }

    try {
      final user = await ref.read(authRepositoryProvider).me();
      state = AsyncData(user);
    } catch (_) {
      // Pertahankan data lama jika refresh gagal (offline, dll).
    }
  }
}
