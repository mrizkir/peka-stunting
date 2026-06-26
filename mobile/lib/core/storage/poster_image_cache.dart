import 'dart:io';

import 'package:dio/dio.dart';
import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

import 'education_content_cache.dart';

class PosterImageCache {
  PosterImageCache(this._contentCache, this._dio);

  final EducationContentCache _contentCache;
  final Dio _dio;
  String? _directoryPath;
  final Map<String, Future<String?>> _inFlightDownloads = {};

  static String fileNameForUrl(String url) {
    final uri = Uri.tryParse(url);
    final ext = uri != null ? p.extension(uri.path) : '';
    final normalizedExt =
        ext.isNotEmpty && ext.length <= 5 ? ext.toLowerCase() : '.jpg';
    return '${url.hashCode.abs()}$normalizedExt';
  }

  Future<String> _cacheDirectory() async {
    if (_directoryPath != null) {
      return _directoryPath!;
    }

    final base = await getDatabasesPath();
    final dir = Directory(p.join(base, 'poster_images'));
    if (!await dir.exists()) {
      await dir.create(recursive: true);
    }
    _directoryPath = dir.path;
    return _directoryPath!;
  }

  Future<String?> resolveLocalPath(String url) async {
    final storedPath = await _contentCache.getPosterLocalPath(url);
    if (storedPath == null) {
      return null;
    }
    if (await File(storedPath).exists()) {
      return storedPath;
    }
    return null;
  }

  Future<void> cacheUrls(Iterable<String> urls) async {
    final uniqueUrls = urls.toSet();
    await Future.wait(uniqueUrls.map(cacheUrl));
    await _contentCache.deletePosterFilesNotIn(uniqueUrls);
  }

  /// Unduh poster ke disk. Request paralel untuk URL sama digabung.
  /// Mengembalikan path lokal jika berhasil, null jika gagal.
  Future<String?> cacheUrl(String url) async {
    final trimmed = url.trim();
    if (trimmed.isEmpty) {
      return null;
    }

    final existing = await resolveLocalPath(trimmed);
    if (existing != null) {
      return existing;
    }

    final inFlight = _inFlightDownloads[trimmed];
    if (inFlight != null) {
      return inFlight;
    }

    final download = _downloadWithRetry(trimmed);
    _inFlightDownloads[trimmed] = download;
    try {
      return await download;
    } finally {
      _inFlightDownloads.remove(trimmed);
    }
  }

  Future<void> invalidateUrl(String url) async {
    final trimmed = url.trim();
    if (trimmed.isEmpty) {
      return;
    }

    final storedPath = await _contentCache.getPosterLocalPath(trimmed);
    if (storedPath != null) {
      final file = File(storedPath);
      if (await file.exists()) {
        await file.delete();
      }
    }
    await _contentCache.deletePosterFile(trimmed);
  }

  Future<String?> _downloadWithRetry(String url) async {
    const maxAttempts = 3;

    for (var attempt = 0; attempt < maxAttempts; attempt++) {
      final localPath = await _downloadOnce(url);
      if (localPath != null) {
        return localPath;
      }
      if (attempt < maxAttempts - 1) {
        await Future<void>.delayed(
          Duration(milliseconds: 600 * (attempt + 1)),
        );
      }
    }
    return null;
  }

  Future<String?> _downloadOnce(String url) async {
    try {
      final response = await _dio.get<List<int>>(
        url,
        options: Options(
          responseType: ResponseType.bytes,
          followRedirects: true,
          validateStatus: (status) => status != null && status < 400,
        ),
      );
      final bytes = response.data;
      if (bytes == null || bytes.isEmpty) {
        return null;
      }

      final directory = await _cacheDirectory();
      final filePath = p.join(directory, fileNameForUrl(url));
      await File(filePath).writeAsBytes(bytes, flush: true);
      await _contentCache.upsertPosterFile(url: url, localPath: filePath);
      return filePath;
    } catch (_) {
      return null;
    }
  }
}
