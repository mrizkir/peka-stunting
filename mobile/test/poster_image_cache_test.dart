import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/core/storage/poster_image_cache.dart';

void main() {
  group('PosterImageCache.fileNameForUrl', () {
    test('uses stable hash-based filename with extension', () {
      const url = 'https://example.com/posters/menu-1.png';
      final fileName = PosterImageCache.fileNameForUrl(url);

      expect(fileName, '${url.hashCode.abs()}.png');
      expect(PosterImageCache.fileNameForUrl(url), fileName);
    });

    test('falls back to jpg when extension is missing', () {
      const url = 'https://example.com/posters/menu-1';
      final fileName = PosterImageCache.fileNameForUrl(url);

      expect(fileName, '${url.hashCode.abs()}.jpg');
    });
  });
}
