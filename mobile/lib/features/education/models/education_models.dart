class EducationMenu {
  EducationMenu({
    required this.id,
    required this.name,
    required this.slug,
    required this.publishedContentsCount,
  });

  final int id;
  final String name;
  final String slug;
  final int publishedContentsCount;

  factory EducationMenu.fromJson(Map<String, dynamic> json) {
    return EducationMenu(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      publishedContentsCount: json['published_contents_count'] as int? ?? 0,
    );
  }
}

class EducationMenuDetail {
  EducationMenuDetail({
    required this.id,
    required this.name,
    required this.slug,
    required this.sections,
    required this.items,
  });

  final int id;
  final String name;
  final String slug;
  final List<EducationSection> sections;
  final List<EducationItemSummary> items;

  factory EducationMenuDetail.fromJson(Map<String, dynamic> json) {
    return EducationMenuDetail(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      sections: (json['sections'] as List<dynamic>? ?? [])
          .map((e) => EducationSection.fromJson(e as Map<String, dynamic>))
          .toList(),
      items: (json['items'] as List<dynamic>? ?? [])
          .map((e) => EducationItemSummary.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class EducationSection {
  EducationSection({
    required this.name,
    required this.slug,
    required this.items,
  });

  final String name;
  final String slug;
  final List<EducationItemSummary> items;

  factory EducationSection.fromJson(Map<String, dynamic> json) {
    return EducationSection(
      name: json['name'] as String,
      slug: json['slug'] as String,
      items: (json['items'] as List<dynamic>? ?? [])
          .map((e) => EducationItemSummary.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class EducationItemSummary {
  EducationItemSummary({
    required this.name,
    required this.slug,
    required this.type,
    this.excerpt,
  });

  final String name;
  final String slug;
  final String type;
  final String? excerpt;

  factory EducationItemSummary.fromJson(Map<String, dynamic> json) {
    return EducationItemSummary(
      name: json['name'] as String,
      slug: json['slug'] as String,
      type: json['type'] as String? ?? 'content',
      excerpt: json['excerpt'] as String?,
    );
  }
}

class EducationContentDetail {
  EducationContentDetail({
    required this.title,
    required this.excerpt,
    required this.body,
    required this.type,
    this.featuredImageUrl,
  });

  final String title;
  final String? excerpt;
  final String? body;
  final String type;
  final String? featuredImageUrl;

  factory EducationContentDetail.fromJson(Map<String, dynamic> json) {
    return EducationContentDetail(
      title: json['title'] as String,
      excerpt: json['excerpt'] as String?,
      body: json['body'] as String?,
      type: json['type'] as String? ?? 'content',
      featuredImageUrl: json['featured_image_url'] as String?,
    );
  }
}
