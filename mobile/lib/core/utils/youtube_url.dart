/// Utilitas parsing URL video edukasi (YouTube).
class YoutubeUrl {
  YoutubeUrl._();

  static String? videoId(String? url) {
    final trimmed = url?.trim();
    if (trimmed == null || trimmed.isEmpty) {
      return null;
    }

    final uri = Uri.tryParse(trimmed);
    if (uri == null) {
      return null;
    }

    final host = uri.host.toLowerCase();
    if (host.contains('youtu.be')) {
      final id = uri.pathSegments.isNotEmpty ? uri.pathSegments.first : '';
      return _isValidId(id) ? id : null;
    }

    if (host.contains('youtube.com') || host.contains('youtube-nocookie.com')) {
      final queryId = uri.queryParameters['v'];
      if (queryId != null && _isValidId(queryId)) {
        return queryId;
      }

      for (final segment in uri.pathSegments) {
        if (_isValidId(segment)) {
          return segment;
        }
      }

      final embedMatch = RegExp(r'/(embed|shorts|live)/([A-Za-z0-9_-]{11})')
          .firstMatch(uri.path);
      if (embedMatch != null) {
        return embedMatch.group(2);
      }
    }

    return null;
  }

  static String? thumbnailUrl(String? url) {
    final id = videoId(url);
    return id != null ? 'https://img.youtube.com/vi/$id/hqdefault.jpg' : null;
  }

  static bool _isValidId(String id) {
    return RegExp(r'^[A-Za-z0-9_-]{11}$').hasMatch(id);
  }
}
