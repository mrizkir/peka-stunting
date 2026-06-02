import 'dart:convert';
import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../../../core/storage/cache_providers.dart';
import '../../../core/storage/education_content_cache.dart';
import '../../../core/storage/poster_image_cache.dart';
import '../kebutuhan_mu_config.dart';
import '../models/kebutuhan_mu_models.dart';

final kebutuhanMuRepositoryProvider = Provider<KebutuhanMuRepository>((ref) {
  return KebutuhanMuRepository(
    ref.read(dioProvider),
    ref.read(educationContentCacheProvider),
    ref.read(posterImageCacheProvider),
  );
});

class KebutuhanMuContentSnapshot {
  const KebutuhanMuContentSnapshot({
    required this.content,
    required this.isFromCache,
    this.fetchedAt,
  });

  final KebutuhanMuContent content;
  final bool isFromCache;
  final DateTime? fetchedAt;
}

class KebutuhanMuTaxonomySnapshot<T> {
  const KebutuhanMuTaxonomySnapshot({
    required this.data,
    required this.isFromCache,
    this.fetchedAt,
  });

  final T data;
  final bool isFromCache;
  final DateTime? fetchedAt;
}

class KebutuhanMuRepository {
  KebutuhanMuRepository(this._dio, this._cache, this._posterCache);

  final Dio _dio;
  final EducationContentCache _cache;
  final PosterImageCache _posterCache;

  Future<List<KebutuhanMuMenuSummary>> fetchTargetGroups() async {
    return _fetchRemoteTargetGroups();
  }

  Future<KebutuhanMuMenuDetail> fetchMenuDetail(String menuSlug) async {
    return _fetchRemoteMenuDetail(menuSlug);
  }

  Stream<KebutuhanMuTaxonomySnapshot<List<KebutuhanMuMenuSummary>>?>
      watchTargetGroups() async* {
    final cached = await _cache.getMenus();
    KebutuhanMuTaxonomySnapshot<List<KebutuhanMuMenuSummary>>? cachedSnapshot;
    if (cached != null) {
      cachedSnapshot = KebutuhanMuTaxonomySnapshot(
        data: cached.items
            .map(KebutuhanMuMenuSummary.fromJson)
            .where((menu) => !KebutuhanMuConfig.excludedMenuSlugs.contains(menu.slug))
            .toList(),
        isFromCache: true,
        fetchedAt: cached.fetchedAt,
      );
      yield cachedSnapshot;
    }

    try {
      final freshData = await _fetchRemoteTargetGroups();
      final freshSnapshot = KebutuhanMuTaxonomySnapshot(
        data: freshData,
        isFromCache: false,
        fetchedAt: DateTime.now(),
      );
      if (cachedSnapshot == null ||
          !_groupsEquals(cachedSnapshot.data, freshSnapshot.data)) {
        yield freshSnapshot;
      }
    } on DioException {
      if (cachedSnapshot == null) {
        yield null;
      }
    }
  }

  Stream<KebutuhanMuTaxonomySnapshot<KebutuhanMuMenuDetail>?> watchMenuDetail(
    String menuSlug,
  ) async* {
    final cached = await _cache.getMenuDetail(menuSlug);
    KebutuhanMuTaxonomySnapshot<KebutuhanMuMenuDetail>? cachedSnapshot;
    if (cached != null) {
      cachedSnapshot = KebutuhanMuTaxonomySnapshot(
        data: KebutuhanMuMenuDetail.fromJson(cached.data),
        isFromCache: true,
        fetchedAt: cached.fetchedAt,
      );
      yield cachedSnapshot;
    }

    try {
      final freshData = await _fetchRemoteMenuDetail(menuSlug);
      final freshSnapshot = KebutuhanMuTaxonomySnapshot(
        data: freshData,
        isFromCache: false,
        fetchedAt: DateTime.now(),
      );
      if (cachedSnapshot == null ||
          !_menuDetailEquals(cachedSnapshot.data, freshSnapshot.data)) {
        yield freshSnapshot;
      }
    } on DioException {
      if (cachedSnapshot == null) {
        yield null;
      }
    }
  }

  Future<KebutuhanMuContent> fetchContent({
    required String menuSlug,
    required String itemSlug,
  }) async {
    try {
      return await _fetchRemoteContent(menuSlug: menuSlug, itemSlug: itemSlug);
    } on DioException {
      final cached = await _cache.get(menuSlug: menuSlug, itemSlug: itemSlug);
      if (cached != null) {
        return _fromCached(cached);
      }
      rethrow;
    }
  }

  Stream<KebutuhanMuContentSnapshot?> watchContent({
    required String menuSlug,
    required String itemSlug,
  }) async* {
    final cached = await _cache.get(menuSlug: menuSlug, itemSlug: itemSlug);
    KebutuhanMuContentSnapshot? cachedSnapshot;
    if (cached != null) {
      cachedSnapshot = KebutuhanMuContentSnapshot(
        content: _fromCached(cached),
        isFromCache: true,
        fetchedAt: cached.fetchedAt,
      );
      yield cachedSnapshot;
    }

    try {
      final fresh = await _fetchRemoteContent(menuSlug: menuSlug, itemSlug: itemSlug);
      final freshSnapshot = KebutuhanMuContentSnapshot(
        content: fresh,
        isFromCache: false,
        fetchedAt: DateTime.now(),
      );
      if (cachedSnapshot == null ||
          !_contentEquals(cachedSnapshot.content, freshSnapshot.content)) {
        yield freshSnapshot;
      }
    } on DioException {
      if (cachedSnapshot == null) {
        yield null;
      }
    }
  }

  Future<KebutuhanMuContent> _fetchRemoteContent({
    required String menuSlug,
    required String itemSlug,
  }) async {
    try {
      final response = await _dio.get(
        '/education/menus/$menuSlug/contents/$itemSlug',
      );
      final data = parseApiData(response.data);
      final content = KebutuhanMuContent.fromJson(data);
      await _cache.upsert(
        menuSlug: menuSlug,
        itemSlug: itemSlug,
        title: content.title,
        excerpt: content.excerpt,
        body: content.body,
        anjuranRulesJson: content.anjuranRules.isEmpty
            ? null
            : jsonEncode(content.anjuranRules),
        posterUrls: content.posterImages,
      );
      unawaited(_posterCache.cacheUrls(content.posterImages));
      return content;
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<List<KebutuhanMuMenuSummary>> _fetchRemoteTargetGroups() async {
    try {
      final response = await _dio.get('/education/menus');
      final list = parseApiList(response.data);
      final normalized = list
          .whereType<Map>()
          .map((item) => item.cast<String, dynamic>())
          .toList();
      await _cache.upsertMenus(normalized);
      return normalized
          .map(KebutuhanMuMenuSummary.fromJson)
          .where((menu) => !KebutuhanMuConfig.excludedMenuSlugs.contains(menu.slug))
          .toList();
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  Future<KebutuhanMuMenuDetail> _fetchRemoteMenuDetail(String menuSlug) async {
    try {
      final response = await _dio.get('/education/menus/$menuSlug');
      final data = parseApiData(response.data);
      await _cache.upsertMenuDetail(menuSlug: menuSlug, data: data);
      return KebutuhanMuMenuDetail.fromJson(data);
    } on DioException catch (error) {
      rethrowApi(error);
    }
  }

  KebutuhanMuContent _fromCached(CachedEducationContent cached) {
    List<Map<String, dynamic>> anjuranRules = const [];
    final rawRules = cached.anjuranRulesJson;
    if (rawRules != null && rawRules.isNotEmpty) {
      final decoded = jsonDecode(rawRules);
      if (decoded is List) {
        anjuranRules = decoded
            .whereType<Map>()
            .map((e) => e.cast<String, dynamic>())
            .toList();
      }
    }

    return KebutuhanMuContent(
      title: cached.title,
      excerpt: cached.excerpt,
      body: cached.body,
      featuredImageUrl: null,
      secondaryImageUrl: null,
      posterImages: cached.posterUrls,
      calculatorConfig: null,
      anjuranRules: anjuranRules,
    );
  }

  bool _contentEquals(KebutuhanMuContent? a, KebutuhanMuContent b) {
    if (a == null) {
      return false;
    }
    return a.title == b.title &&
        a.excerpt == b.excerpt &&
        a.body == b.body &&
        _posterUrlsEquals(a.posterImages, b.posterImages) &&
        _anjuranRulesEquals(a.anjuranRules, b.anjuranRules);
  }

  bool _posterUrlsEquals(List<String> a, List<String> b) {
    if (a.length != b.length) {
      return false;
    }
    for (var i = 0; i < a.length; i++) {
      if (a[i] != b[i]) {
        return false;
      }
    }
    return true;
  }

  bool _anjuranRulesEquals(
    List<Map<String, dynamic>> a,
    List<Map<String, dynamic>> b,
  ) {
    if (a.length != b.length) {
      return false;
    }
    for (var i = 0; i < a.length; i++) {
      if (jsonEncode(a[i]) != jsonEncode(b[i])) {
        return false;
      }
    }
    return true;
  }

  bool _groupsEquals(
    List<KebutuhanMuMenuSummary> a,
    List<KebutuhanMuMenuSummary> b,
  ) {
    if (a.length != b.length) {
      return false;
    }
    for (var i = 0; i < a.length; i++) {
      if (a[i].id != b[i].id ||
          a[i].slug != b[i].slug ||
          a[i].name != b[i].name ||
          a[i].publishedContentsCount != b[i].publishedContentsCount) {
        return false;
      }
    }
    return true;
  }

  bool _menuDetailEquals(KebutuhanMuMenuDetail a, KebutuhanMuMenuDetail b) {
    if (a.slug != b.slug ||
        a.name != b.name ||
        a.description != b.description ||
        a.sections.length != b.sections.length ||
        a.items.length != b.items.length) {
      return false;
    }

    for (var i = 0; i < a.sections.length; i++) {
      final sa = a.sections[i];
      final sb = b.sections[i];
      if (sa.slug != sb.slug ||
          sa.name != sb.name ||
          sa.items.length != sb.items.length) {
        return false;
      }

      for (var j = 0; j < sa.items.length; j++) {
        final ia = sa.items[j];
        final ib = sb.items[j];
        if (ia.slug != ib.slug ||
            ia.name != ib.name ||
            ia.type != ib.type ||
            ia.excerpt != ib.excerpt) {
          return false;
        }
      }
    }

    return true;
  }
}
