class KebutuhanMuMenuSummary {
  KebutuhanMuMenuSummary({
    required this.id,
    required this.name,
    required this.slug,
    required this.publishedContentsCount,
  });

  final int id;
  final String name;
  final String slug;
  final int publishedContentsCount;

  factory KebutuhanMuMenuSummary.fromJson(Map<String, dynamic> json) {
    return KebutuhanMuMenuSummary(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      publishedContentsCount: json['published_contents_count'] as int? ?? 0,
    );
  }
}

class KebutuhanMuItem {
  KebutuhanMuItem({
    required this.name,
    required this.slug,
    required this.type,
    this.excerpt,
  });

  final String name;
  final String slug;
  final String type;
  final String? excerpt;

  factory KebutuhanMuItem.fromJson(Map<String, dynamic> json) {
    return KebutuhanMuItem(
      name: json['name'] as String,
      slug: json['slug'] as String,
      type: json['type'] as String? ?? 'content',
      excerpt: json['excerpt'] as String?,
    );
  }
}

class KebutuhanMuSection {
  KebutuhanMuSection({
    required this.name,
    required this.slug,
    required this.items,
  });

  final String name;
  final String slug;
  final List<KebutuhanMuItem> items;

  factory KebutuhanMuSection.fromJson(Map<String, dynamic> json) {
    return KebutuhanMuSection(
      name: json['name'] as String,
      slug: json['slug'] as String,
      items: (json['items'] as List<dynamic>? ?? [])
          .map((e) => KebutuhanMuItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class KebutuhanMuMenuDetail {
  KebutuhanMuMenuDetail({
    required this.name,
    required this.slug,
    this.description,
    required this.sections,
    required this.items,
  });

  final String name;
  final String slug;
  final String? description;
  final List<KebutuhanMuSection> sections;
  final List<KebutuhanMuItem> items;

  factory KebutuhanMuMenuDetail.fromJson(Map<String, dynamic> json) {
    final description = json['description'] as String?;

    return KebutuhanMuMenuDetail(
      name: json['name'] as String,
      slug: json['slug'] as String,
      description: description != null && description.trim().isNotEmpty
          ? description.trim()
          : null,
      sections: (json['sections'] as List<dynamic>? ?? [])
          .map((e) => KebutuhanMuSection.fromJson(e as Map<String, dynamic>))
          .toList(),
      items: (json['items'] as List<dynamic>? ?? [])
          .map((e) => KebutuhanMuItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class KebutuhanMuContent {
  KebutuhanMuContent({
    required this.title,
    required this.excerpt,
    required this.body,
    this.videoUrl,
    this.featuredImageUrl,
    this.secondaryImageUrl,
    required this.posterImages,
    this.calculatorConfig,
  });

  final String title;
  final String? excerpt;
  final String? body;
  final String? videoUrl;
  final String? featuredImageUrl;
  final String? secondaryImageUrl;
  final List<String> posterImages;
  final Map<String, dynamic>? calculatorConfig;

  factory KebutuhanMuContent.fromJson(Map<String, dynamic> json) {
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

    return KebutuhanMuContent(
      title: json['title'] as String,
      excerpt: json['excerpt'] as String?,
      body: json['body'] as String?,
      videoUrl: videoUrl != null && videoUrl.trim().isNotEmpty
          ? videoUrl.trim()
          : null,
      featuredImageUrl: featuredImageUrl,
      secondaryImageUrl: secondaryImageUrl,
      posterImages: posterImages,
      calculatorConfig: json['calculator_config'] as Map<String, dynamic>?,
    );
  }
}
