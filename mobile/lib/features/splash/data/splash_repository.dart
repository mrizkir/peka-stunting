import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../../../core/storage/cache_providers.dart';
import '../../../core/storage/poster_image_cache.dart';
import '../../../core/storage/splash_storage.dart';
import '../models/splash_image_data.dart';

final splashStorageProvider = Provider<SplashStorage>((ref) => SplashStorage());

final splashRepositoryProvider = Provider<SplashRepository>((ref) {
  return SplashRepository(
    ref.read(dioProvider),
    ref.read(splashStorageProvider),
    ref.read(posterImageCacheProvider),
  );
});

class SplashRepository {
  SplashRepository(
    this._dio,
    this._storage,
    this._imageCache,
  );

  final Dio _dio;
  final SplashStorage _storage;
  final PosterImageCache _imageCache;

  /// Muat splash: cache disk dulu (cepat), lalu sinkronkan ke API.
  Future<SplashImageData> loadSplashImage() async {
    final cachedUrl = await _storage.readCachedUrl();
    final cachedLocalPath = cachedUrl != null
        ? await _imageCache.resolveLocalPath(cachedUrl)
        : null;

    if (cachedLocalPath != null && cachedUrl != null) {
      _refreshFromApiInBackground();
      return SplashImageData(
        remoteUrl: cachedUrl,
        localPath: cachedLocalPath,
      );
    }

    return _fetchAndCache();
  }

  Future<SplashImageData> _fetchAndCache() async {
    final url = await _fetchSplashImageUrlFromApi();

    if (url == null || url.isEmpty) {
      await _storage.clearCachedUrl();
      return const SplashImageData();
    }

    await _storage.saveCachedUrl(url);
    await _imageCache.cacheUrl(url);
    final localPath = await _imageCache.resolveLocalPath(url);

    return SplashImageData(
      remoteUrl: url,
      localPath: localPath,
    );
  }

  void _refreshFromApiInBackground() {
    Future<void>(() async {
      try {
        await _fetchAndCache();
      } catch (_) {
        // Pertahankan cache lama jika refresh gagal.
      }
    });
  }

  Future<String?> _fetchSplashImageUrlFromApi() async {
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
