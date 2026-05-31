class CalculatorAnjuranRule {
  static const metricBmi = 'bmi';
  static const metricLilaCm = 'lila_cm';
  static const metricYesCount = 'yes_count';
  static const metricZScore = 'z_score';

  static const indicatorHeightForAge = 'height_for_age';
  static const indicatorWeightForAge = 'weight_for_age';
  static const indicatorWeightForHeight = 'weight_for_height';
  static const indicatorPrimary = 'primary';

  static const operatorGt = 'gt';
  static const operatorGte = 'gte';
  static const operatorLt = 'lt';
  static const operatorLte = 'lte';

  CalculatorAnjuranRule({
    required this.sortOrder,
    required this.metric,
    this.indicator,
    this.threshold,
    required this.operator,
    required this.isDefault,
    required this.label,
    this.slug,
    required this.anjuran,
  });

  final int sortOrder;
  final String metric;
  final String? indicator;
  final double? threshold;
  final String operator;
  final bool isDefault;
  final String label;
  final String? slug;
  final String anjuran;

  factory CalculatorAnjuranRule.fromJson(Map<String, dynamic> json) {
    return CalculatorAnjuranRule(
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
      metric: json['metric'] as String? ?? metricBmi,
      indicator: json['indicator'] as String?,
      threshold: (json['threshold'] as num?)?.toDouble(),
      operator: json['operator'] as String? ?? operatorGt,
      isDefault: json['is_default'] == true,
      label: json['label'] as String? ?? '',
      slug: json['slug'] as String?,
      anjuran: json['anjuran'] as String? ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'sort_order': sortOrder,
      'metric': metric,
      'indicator': indicator,
      'threshold': threshold,
      'operator': operator,
      'is_default': isDefault,
      'label': label,
      'slug': slug,
      'anjuran': anjuran,
    };
  }
}

class ResolvedAnjuran {
  const ResolvedAnjuran({
    required this.slug,
    required this.label,
    required this.anjuran,
  });

  final String slug;
  final String label;
  final String anjuran;
}
