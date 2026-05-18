class ChildSummary {
  ChildSummary({
    required this.id,
    required this.name,
    required this.gender,
    required this.birthDate,
    this.village,
    this.latestMeasurement,
    this.latestRisk,
  });

  final int id;
  final String name;
  final String gender;
  final String birthDate;
  final String? village;
  final MeasurementSummary? latestMeasurement;
  final RiskSummary? latestRisk;

  factory ChildSummary.fromJson(Map<String, dynamic> json) {
    return ChildSummary(
      id: json['id'] as int,
      name: json['name'] as String,
      gender: json['gender'] as String,
      birthDate: json['birth_date'] as String,
      village: json['village'] as String?,
      latestMeasurement: json['latest_measurement'] != null
          ? MeasurementSummary.fromJson(
              json['latest_measurement'] as Map<String, dynamic>,
            )
          : null,
      latestRisk: json['latest_risk_assessment'] != null
          ? RiskSummary.fromJson(
              json['latest_risk_assessment'] as Map<String, dynamic>,
            )
          : null,
    );
  }
}

class ChildDetail extends ChildSummary {
  ChildDetail({
    required super.id,
    required super.name,
    required super.gender,
    required super.birthDate,
    super.village,
    super.latestMeasurement,
    super.latestRisk,
    this.posyandu,
    this.nik,
    this.guardianName,
  });

  final String? posyandu;
  final String? nik;
  final String? guardianName;

  factory ChildDetail.fromJson(Map<String, dynamic> json) {
    final guardian = json['guardian'] as Map<String, dynamic>?;
    return ChildDetail(
      id: json['id'] as int,
      name: json['name'] as String,
      gender: json['gender'] as String,
      birthDate: json['birth_date'] as String,
      village: json['village'] as String?,
      posyandu: json['posyandu'] as String?,
      nik: json['nik'] as String?,
      guardianName: guardian?['name'] as String?,
      latestMeasurement: json['latest_measurement'] != null
          ? MeasurementSummary.fromJson(
              json['latest_measurement'] as Map<String, dynamic>,
            )
          : null,
      latestRisk: json['latest_risk_assessment'] != null
          ? RiskSummary.fromJson(
              json['latest_risk_assessment'] as Map<String, dynamic>,
            )
          : null,
    );
  }
}

class MeasurementSummary {
  MeasurementSummary({
    required this.measuredAt,
    required this.weightKg,
    required this.heightCm,
    required this.ageMonths,
  });

  final String measuredAt;
  final double weightKg;
  final double heightCm;
  final int ageMonths;

  factory MeasurementSummary.fromJson(Map<String, dynamic> json) {
    return MeasurementSummary(
      measuredAt: json['measured_at'] as String,
      weightKg: (json['weight_kg'] as num).toDouble(),
      heightCm: (json['height_cm'] as num).toDouble(),
      ageMonths: json['age_months'] as int,
    );
  }
}

class RiskSummary {
  RiskSummary({
    required this.status,
    required this.statusLabel,
    required this.score,
    this.summary,
  });

  final String status;
  final String statusLabel;
  final int score;
  final String? summary;

  factory RiskSummary.fromJson(Map<String, dynamic> json) {
    return RiskSummary(
      status: json['status'] as String,
      statusLabel: json['status_label'] as String? ?? json['status'] as String,
      score: json['score'] as int,
      summary: json['summary'] as String?,
    );
  }

  ColorHint get hint {
    switch (status) {
      case 'need_follow_up':
        return ColorHint.danger;
      case 'risk':
        return ColorHint.warning;
      default:
        return ColorHint.success;
    }
  }
}

enum ColorHint { success, warning, danger }
