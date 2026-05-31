import 'youtube_url.dart';

enum EducationVideoKind {
  youtube,
  directFile,
  external,
}

/// Klasifikasi URL video edukasi dari CMS.
class EducationVideoUrl {
  EducationVideoUrl._();

  static EducationVideoKind kind(String? url) {
    final trimmed = url?.trim();
    if (trimmed == null || trimmed.isEmpty) {
      return EducationVideoKind.external;
    }

    if (YoutubeUrl.videoId(trimmed) != null) {
      return EducationVideoKind.youtube;
    }

    if (isDirectFile(trimmed)) {
      return EducationVideoKind.directFile;
    }

    return EducationVideoKind.external;
  }

  static bool isDirectFile(String url) {
    final path = Uri.tryParse(url.trim())?.path.toLowerCase() ?? '';

    return RegExp(r'\.(mp4|webm|mov|m3u8|mkv)$').hasMatch(path);
  }
}
