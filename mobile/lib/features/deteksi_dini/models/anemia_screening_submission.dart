class AnemiaScreeningSubmission {
  const AnemiaScreeningSubmission({
    required this.id,
    required this.calculatorSlug,
    required this.menuSlug,
    required this.yesCount,
    required this.totalQuestions,
    required this.riskYesThreshold,
    required this.category,
    required this.categoryLabel,
    required this.submittedAt,
  });

  final int id;
  final String calculatorSlug;
  final String menuSlug;
  final int yesCount;
  final int totalQuestions;
  final int riskYesThreshold;
  final String category;
  final String categoryLabel;
  final String? submittedAt;

  factory AnemiaScreeningSubmission.fromJson(Map<String, dynamic> json) {
    return AnemiaScreeningSubmission(
      id: json['id'] as int,
      calculatorSlug: json['calculator_slug'] as String,
      menuSlug: json['menu_slug'] as String,
      yesCount: json['yes_count'] as int,
      totalQuestions: json['total_questions'] as int,
      riskYesThreshold: json['risk_yes_threshold'] as int,
      category: json['category'] as String,
      categoryLabel: json['category_label'] as String,
      submittedAt: json['submitted_at'] as String?,
    );
  }
}
