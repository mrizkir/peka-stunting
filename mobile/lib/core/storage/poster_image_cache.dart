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

  Future<void> cacheUrl(String url) async {
    final trimmed = url.trim();
    if (trimmed.isEmpty) {
      return;
    }

    final existing = await resolveLocalPath(trimmed);
    if (existing != null) {
      return;
    }

    try {
      final response = await _dio.get<List<int>>(
        trimmed,
        options: Options(
          responseType: ResponseType.bytes,
          followRedirects: true,
          validateStatus: (status) => status != null && status < 400,
        ),
      );
      final bytes = response.data;
      if (bytes == null || bytes.isEmpty) {
        return;
      }

      final directory = await _cacheDirectory();
      final filePath = p.join(directory, fileNameForUrl(trimmed));
      await File(filePath).writeAsBytes(bytes, flush: true);
      await _contentCache.upsertPosterFile(url: trimmed, localPath: filePath);
    } catch (_) {
      // Poster tetap bisa dimuat dari network jika unduhan gagal.
    }
  }
}
