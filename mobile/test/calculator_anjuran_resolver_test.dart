import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/features/deteksi_dini/domain/calculator_anjuran_resolver.dart';
import 'package:peka_stunting/features/deteksi_dini/models/calculator_anjuran_rule.dart';

void main() {
  const resolver = CalculatorAnjuranResolver();

  final rules = [
    CalculatorAnjuranRule(
      sortOrder: 1,
      metric: CalculatorAnjuranRule.metricBmi,
      threshold: 30,
      operator: CalculatorAnjuranRule.operatorGt,
      isDefault: false,
      label: 'Obesitas',
      slug: 'obese',
      anjuran: 'Anjuran obesitas',
    ),
    CalculatorAnjuranRule(
      sortOrder: 2,
      metric: CalculatorAnjuranRule.metricBmi,
      threshold: 25,
      operator: CalculatorAnjuranRule.operatorGt,
      isDefault: false,
      label: 'Gemuk',
      slug: 'overweight',
      anjuran: 'Anjuran gemuk',
    ),
    CalculatorAnjuranRule(
      sortOrder: 3,
      metric: CalculatorAnjuranRule.metricBmi,
      threshold: 18.5,
      operator: CalculatorAnjuranRule.operatorGt,
      isDefault: false,
      label: 'Normal',
      slug: 'normal',
      anjuran: 'Anjuran normal',
    ),
    CalculatorAnjuranRule(
      sortOrder: 4,
      metric: CalculatorAnjuranRule.metricBmi,
      threshold: null,
      operator: CalculatorAnjuranRule.operatorGt,
      isDefault: true,
      label: 'Kurus',
      slug: 'underweight',
      anjuran: 'Anjuran kurus',
    ),
  ];

  test('resolves overweight for bmi 26.5', () {
    final resolved = resolver.resolve(
      rules: rules,
      metric: CalculatorAnjuranRule.metricBmi,
      value: 26.5,
    );

    expect(resolved?.slug, 'overweight');
    expect(resolved?.label, 'Gemuk');
    expect(resolved?.anjuran, 'Anjuran gemuk');
  });

  test('resolves default underweight for bmi 17', () {
    final resolved = resolver.resolve(
      rules: rules,
      metric: CalculatorAnjuranRule.metricBmi,
      value: 17,
    );

    expect(resolved?.slug, 'underweight');
    expect(resolved?.label, 'Kurus');
  });

  test('resolves normal lila at threshold', () {
    final lilaRules = [
      CalculatorAnjuranRule(
        sortOrder: 1,
        metric: CalculatorAnjuranRule.metricLilaCm,
        threshold: 23.5,
        operator: CalculatorAnjuranRule.operatorGte,
        isDefault: false,
        label: 'Normal',
        slug: 'normal',
        anjuran: 'Anjuran normal',
      ),
      CalculatorAnjuranRule(
        sortOrder: 2,
        metric: CalculatorAnjuranRule.metricLilaCm,
        threshold: null,
        operator: CalculatorAnjuranRule.operatorLt,
        isDefault: true,
        label: 'Berisiko KEK',
        slug: 'at_risk',
        anjuran: 'Anjuran KEK',
      ),
    ];

    final resolved = resolver.resolve(
      rules: lilaRules,
      metric: CalculatorAnjuranRule.metricLilaCm,
      value: 23.5,
    );

    expect(resolved?.slug, 'normal');
    expect(resolved?.label, 'Normal');
  });

  test('resolves at risk lila below threshold', () {
    final lilaRules = [
      CalculatorAnjuranRule(
        sortOrder: 1,
        metric: CalculatorAnjuranRule.metricLilaCm,
        threshold: 23.5,
        operator: CalculatorAnjuranRule.operatorGte,
        isDefault: false,
        label: 'Normal',
        slug: 'normal',
        anjuran: 'Anjuran normal',
      ),
      CalculatorAnjuranRule(
        sortOrder: 2,
        metric: CalculatorAnjuranRule.metricLilaCm,
        threshold: null,
        operator: CalculatorAnjuranRule.operatorLt,
        isDefault: true,
        label: 'Berisiko KEK',
        slug: 'at_risk',
        anjuran: 'Anjuran KEK',
      ),
    ];

    final resolved = resolver.resolve(
      rules: lilaRules,
      metric: CalculatorAnjuranRule.metricLilaCm,
      value: 22.0,
    );

    expect(resolved?.slug, 'at_risk');
    expect(resolved?.anjuran, 'Anjuran KEK');
  });

  test('resolves anemia medium risk for 5 yes answers', () {
    final anemiaRules = [
      CalculatorAnjuranRule(
        sortOrder: 1,
        metric: CalculatorAnjuranRule.metricYesCount,
        threshold: 7,
        operator: CalculatorAnjuranRule.operatorGt,
        isDefault: false,
        label: 'Resiko Tinggi Anemia',
        slug: 'high_risk',
        anjuran: 'Anjuran tinggi',
      ),
      CalculatorAnjuranRule(
        sortOrder: 2,
        metric: CalculatorAnjuranRule.metricYesCount,
        threshold: 4,
        operator: CalculatorAnjuranRule.operatorGte,
        isDefault: false,
        label: 'Risiko Sedang Anemia',
        slug: 'medium_risk',
        anjuran: 'Anjuran sedang',
      ),
      CalculatorAnjuranRule(
        sortOrder: 3,
        metric: CalculatorAnjuranRule.metricYesCount,
        threshold: 1,
        operator: CalculatorAnjuranRule.operatorGte,
        isDefault: false,
        label: 'Risiko Rendah Anemia',
        slug: 'low_risk',
        anjuran: 'Anjuran rendah',
      ),
      CalculatorAnjuranRule(
        sortOrder: 4,
        metric: CalculatorAnjuranRule.metricYesCount,
        threshold: null,
        operator: CalculatorAnjuranRule.operatorGte,
        isDefault: true,
        label: 'Tidak ada resiko Anemia',
        slug: 'normal',
        anjuran: 'Anjuran normal',
      ),
    ];

    final resolved = resolver.resolve(
      rules: anemiaRules,
      metric: CalculatorAnjuranRule.metricYesCount,
      value: 5,
    );

    expect(resolved?.slug, 'medium_risk');
    expect(resolved?.anjuran, 'Anjuran sedang');
  });

  test('resolves height for age stunted z score', () {
    final rules = [
      CalculatorAnjuranRule(
        sortOrder: 1,
        metric: CalculatorAnjuranRule.metricZScore,
        indicator: CalculatorAnjuranRule.indicatorHeightForAge,
        threshold: -2,
        operator: CalculatorAnjuranRule.operatorLt,
        isDefault: false,
        label: 'Pendek (stunting)',
        slug: 'stunted',
        anjuran: 'Anjuran stunting',
      ),
      CalculatorAnjuranRule(
        sortOrder: 2,
        metric: CalculatorAnjuranRule.metricZScore,
        indicator: CalculatorAnjuranRule.indicatorHeightForAge,
        threshold: null,
        operator: CalculatorAnjuranRule.operatorGte,
        isDefault: true,
        label: 'Normal',
        slug: 'normal',
        anjuran: 'Anjuran normal',
      ),
    ];

    final resolved = resolver.resolve(
      rules: rules,
      metric: CalculatorAnjuranRule.metricZScore,
      value: -2.5,
      indicator: CalculatorAnjuranRule.indicatorHeightForAge,
    );

    expect(resolved?.slug, 'stunted');
  });
}
