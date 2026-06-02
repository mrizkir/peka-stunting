class AppInfoContent {
  AppInfoContent({
    required this.title,
    required this.excerpt,
    required this.body,
    this.videoUrl,
    this.featuredImageUrl,
    this.secondaryImageUrl,
    required this.posterImages,
  });

  final String title;
  final String? excerpt;
  final String? body;
  final String? videoUrl;
  final String? featuredImageUrl;
  final String? secondaryImageUrl;
  final List<String> posterImages;

  factory AppInfoContent.fromJson(Map<String, dynamic> json) {
    final featuredImageUrl = json['featured_image_url'] as String?;
    final secondaryImageUrl = json['secondary_image_url'] as String?;
    final videoUrl = json['video_url'] as String?;
    final posterImages = (json['poster_images'] as List<dynamic>? ?? [])
        .map((e) => e.toString())
        .where((url) => url.trim().isNotEmpty)
        .toList();

    if (posterImages.isEmpty) {
      if (featuredImageUrl != null && featuredImageUrl.trim().isNotEmpty) {
        posterImages.add(featuredImageUrl);
      }
      if (secondaryImageUrl != null && secondaryImageUrl.trim().isNotEmpty) {
        posterImages.add(secondaryImageUrl);
      }
    }

    return AppInfoContent(
      title: json['title'] as String,
      excerpt: json['excerpt'] as String?,
      body: json['body'] as String?,
      videoUrl: videoUrl != null && videoUrl.trim().isNotEmpty
          ? videoUrl.trim()
          : null,
      featuredImageUrl: featuredImageUrl,
      secondaryImageUrl: secondaryImageUrl,
      posterImages: posterImages,
    );
  }
}
