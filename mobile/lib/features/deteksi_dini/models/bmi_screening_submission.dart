class BmiScreeningSubmission {
  const BmiScreeningSubmission({
    required this.id,
    required this.calculatorSlug,
    required this.menuSlug,
    required this.category,
    required this.categoryLabel,
    required this.bmi,
    required this.weightKg,
    required this.heightCm,
    required this.submittedAt,
  });

  final int id;
  final String calculatorSlug;
  final String menuSlug;
  final String category;
  final String categoryLabel;
  final double bmi;
  final double weightKg;
  final double heightCm;
  final String? submittedAt;

  factory BmiScreeningSubmission.fromJson(Map<String, dynamic> json) {
    final answers = json['answers'] as Map<String, dynamic>? ?? const {};

    return BmiScreeningSubmission(
      id: json['id'] as int,
      calculatorSlug: json['calculator_slug'] as String,
      menuSlug: json['menu_slug'] as String,
      category: json['category'] as String,
      categoryLabel: json['category_label'] as String,
      bmi: (answers['bmi'] as num?)?.toDouble() ?? 0,
      weightKg: (answers['weight_kg'] as num?)?.toDouble() ?? 0,
      heightCm: (answers['height_cm'] as num?)?.toDouble() ?? 0,
      submittedAt: json['submitted_at'] as String?,
    );
  }
}
