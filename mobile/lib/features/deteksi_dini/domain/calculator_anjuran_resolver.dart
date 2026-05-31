import '../models/calculator_anjuran_rule.dart';

class CalculatorAnjuranResolver {
  const CalculatorAnjuranResolver();

  ResolvedAnjuran? resolve({
    required List<CalculatorAnjuranRule> rules,
    required String metric,
    required double value,
    String? indicator,
  }) {
    if (rules.isEmpty) {
      return null;
    }

    final filtered = rules
        .where((rule) => rule.metric == metric)
        .where(
          (rule) => indicator == null
              ? rule.indicator == null
              : rule.indicator == indicator,
        )
        .toList()
      ..sort((a, b) => a.sortOrder.compareTo(b.sortOrder));

    if (filtered.isEmpty) {
      return null;
    }

    CalculatorAnjuranRule? defaultRule;

    for (final rule in filtered) {
      if (rule.isDefault) {
        defaultRule = rule;
        continue;
      }

      if (_matches(rule, value)) {
        return _fromRule(rule);
      }
    }

    if (defaultRule != null) {
      return _fromRule(defaultRule);
    }

    return null;
  }

  bool _matches(CalculatorAnjuranRule rule, double value) {
    final threshold = rule.threshold;
    if (threshold == null) {
      return false;
    }

    switch (rule.operator) {
      case CalculatorAnjuranRule.operatorGt:
        return value > threshold;
      case CalculatorAnjuranRule.operatorGte:
        return value >= threshold;
      case CalculatorAnjuranRule.operatorLt:
        return value < threshold;
      case CalculatorAnjuranRule.operatorLte:
        return value <= threshold;
      default:
        return false;
    }
  }

  ResolvedAnjuran _fromRule(CalculatorAnjuranRule rule) {
    return ResolvedAnjuran(
      slug: rule.slug?.trim().isNotEmpty == true ? rule.slug!.trim() : 'unknown',
      label: rule.label,
      anjuran: rule.anjuran,
    );
  }
}
