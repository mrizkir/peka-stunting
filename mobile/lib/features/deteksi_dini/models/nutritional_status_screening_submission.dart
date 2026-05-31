class NutritionalStatusScreeningSubmission {
  const NutritionalStatusScreeningSubmission({
    required this.id,
    required this.calculatorSlug,
    required this.menuSlug,
    required this.category,
    required this.categoryLabel,
    required this.birthDate,
    required this.gender,
    required this.weightKg,
    required this.heightCm,
    required this.ageMonths,
    required this.heightForAgeZ,
    required this.weightForAgeZ,
    required this.weightForHeightZ,
    required this.submittedAt,
  });

  final int id;
  final String calculatorSlug;
  final String menuSlug;
  final String category;
  final String categoryLabel;
  final String? birthDate;
  final String? gender;
  final double weightKg;
  final double heightCm;
  final int ageMonths;
  final double heightForAgeZ;
  final double weightForAgeZ;
  final double weightForHeightZ;
  final String? submittedAt;

  factory NutritionalStatusScreeningSubmission.fromJson(
    Map<String, dynamic> json,
  ) {
    final answers = json['answers'] as Map<String, dynamic>? ?? const {};

    return NutritionalStatusScreeningSubmission(
      id: json['id'] as int,
      calculatorSlug: json['calculator_slug'] as String,
      menuSlug: json['menu_slug'] as String,
      category: json['category'] as String,
      categoryLabel: json['category_label'] as String,
      birthDate: answers['birth_date'] as String?,
      gender: answers['gender'] as String?,
      weightKg: (answers['weight_kg'] as num?)?.toDouble() ?? 0,
      heightCm: (answers['height_cm'] as num?)?.toDouble() ?? 0,
      ageMonths: (answers['age_months'] as num?)?.toInt() ?? 0,
      heightForAgeZ: (answers['height_for_age_z'] as num?)?.toDouble() ?? 0,
      weightForAgeZ: (answers['weight_for_age_z'] as num?)?.toDouble() ?? 0,
      weightForHeightZ:
          (answers['weight_for_height_z'] as num?)?.toDouble() ?? 0,
      submittedAt: json['submitted_at'] as String?,
    );
  }
}
