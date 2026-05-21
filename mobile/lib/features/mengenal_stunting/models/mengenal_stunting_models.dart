class MengenalStuntingItem {
  MengenalStuntingItem({
    required this.name,
    required this.slug,
    this.excerpt,
  });

  final String name;
  final String slug;
  final String? excerpt;

  factory MengenalStuntingItem.fromJson(Map<String, dynamic> json) {
    return MengenalStuntingItem(
      name: json['name'] as String,
      slug: json['slug'] as String,
      excerpt: json['excerpt'] as String?,
    );
  }
}

class MengenalStuntingMenu {
  MengenalStuntingMenu({
    required this.name,
    required this.slug,
    required this.items,
  });

  final String name;
  final String slug;
  final List<MengenalStuntingItem> items;

  factory MengenalStuntingMenu.fromJson(Map<String, dynamic> json) {
    final rootItems = (json['items'] as List<dynamic>? ?? [])
        .map((e) => MengenalStuntingItem.fromJson(e as Map<String, dynamic>))
        .toList();

    final sectionItems = (json['sections'] as List<dynamic>? ?? [])
        .expand(
          (section) => (section['items'] as List<dynamic>? ?? [])
              .map(
                (e) => MengenalStuntingItem.fromJson(
                  e as Map<String, dynamic>,
                ),
              ),
        )
        .toList();

    return MengenalStuntingMenu(
      name: json['name'] as String,
      slug: json['slug'] as String,
      items: [...rootItems, ...sectionItems],
    );
  }
}

class MengenalStuntingContent {
  MengenalStuntingContent({
    required this.title,
    required this.excerpt,
    required this.body,
    this.featuredImageUrl,
  });

  final String title;
  final String? excerpt;
  final String? body;
  final String? featuredImageUrl;

  factory MengenalStuntingContent.fromJson(Map<String, dynamic> json) {
    return MengenalStuntingContent(
      title: json['title'] as String,
      excerpt: json['excerpt'] as String?,
      body: json['body'] as String?,
      featuredImageUrl: json['featured_image_url'] as String?,
    );
  }
}
