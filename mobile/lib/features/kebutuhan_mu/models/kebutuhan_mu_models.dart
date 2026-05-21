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
    required this.sections,
    required this.items,
  });

  final String name;
  final String slug;
  final List<KebutuhanMuSection> sections;
  final List<KebutuhanMuItem> items;

  factory KebutuhanMuMenuDetail.fromJson(Map<String, dynamic> json) {
    return KebutuhanMuMenuDetail(
      name: json['name'] as String,
      slug: json['slug'] as String,
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
    this.featuredImageUrl,
  });

  final String title;
  final String? excerpt;
  final String? body;
  final String? featuredImageUrl;

  factory KebutuhanMuContent.fromJson(Map<String, dynamic> json) {
    return KebutuhanMuContent(
      title: json['title'] as String,
      excerpt: json['excerpt'] as String?,
      body: json['body'] as String?,
      featuredImageUrl: json['featured_image_url'] as String?,
    );
  }
}
