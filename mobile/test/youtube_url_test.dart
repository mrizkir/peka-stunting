import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/core/utils/youtube_url.dart';

void main() {
  group('YoutubeUrl', () {
    test('extracts id from watch url', () {
      expect(
        YoutubeUrl.videoId('https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
        'dQw4w9WgXcQ',
      );
    });

    test('extracts id from youtu.be url', () {
      expect(
        YoutubeUrl.videoId('https://youtu.be/dQw4w9WgXcQ'),
        'dQw4w9WgXcQ',
      );
    });

    test('returns null for non youtube url', () {
      expect(YoutubeUrl.videoId('https://example.com/video.mp4'), isNull);
    });
  });
}
