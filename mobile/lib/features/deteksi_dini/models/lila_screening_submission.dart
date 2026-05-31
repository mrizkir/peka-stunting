class LilaScreeningSubmission {
  const LilaScreeningSubmission({
    required this.id,
    required this.calculatorSlug,
    required this.menuSlug,
    required this.category,
    required this.categoryLabel,
    required this.ageYears,
    required this.lilaCm,
    required this.submittedAt,
  });

  final int id;
  final String calculatorSlug;
  final String menuSlug;
  final String category;
  final String categoryLabel;
  final int ageYears;
  final double lilaCm;
  final String? submittedAt;

  factory LilaScreeningSubmission.fromJson(Map<String, dynamic> json) {
    final answers = json['answers'] as Map<String, dynamic>? ?? const {};

    return LilaScreeningSubmission(
      id: json['id'] as int,
      calculatorSlug: json['calculator_slug'] as String,
      menuSlug: json['menu_slug'] as String,
      category: json['category'] as String,
      categoryLabel: json['category_label'] as String,
      ageYears: (answers['age_years'] as num?)?.toInt() ?? 0,
      lilaCm: (answers['lila_cm'] as num?)?.toDouble() ?? 0,
      submittedAt: json['submitted_at'] as String?,
    );
  }
}
