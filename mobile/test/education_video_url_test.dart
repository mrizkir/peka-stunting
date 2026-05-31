import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/core/utils/education_video_url.dart';

void main() {
  group('EducationVideoUrl', () {
    test('detects youtube links', () {
      expect(
        EducationVideoUrl.kind(
          'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ),
        EducationVideoKind.youtube,
      );
    });

    test('detects direct mp4 files', () {
      expect(
        EducationVideoUrl.kind('https://example.com/videos/materi.mp4'),
        EducationVideoKind.directFile,
      );
    });

    test('treats unknown links as external', () {
      expect(
        EducationVideoUrl.kind('https://example.com/watch/123'),
        EducationVideoKind.external,
      );
    });
  });
}
