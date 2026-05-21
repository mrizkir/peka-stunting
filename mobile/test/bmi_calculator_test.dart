import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/features/calculators/domain/bmi_calculator.dart';

void main() {
  group('BmiCalculator', () {
    test('returns null for invalid input', () {
      expect(
        BmiCalculator.calculate(weightKg: 0, heightCm: 170),
        isNull,
      );
      expect(
        BmiCalculator.calculate(weightKg: 50, heightCm: 0),
        isNull,
      );
    });

    test('classifies normal BMI', () {
      // 52 kg, 160 cm -> 20.3
      final result = BmiCalculator.calculate(weightKg: 52, heightCm: 160);
      expect(result, isNotNull);
      expect(result!.value, 20.3);
      expect(result.category, BmiCategory.normal);
      expect(result.categoryLabel, 'Normal');
    });

    test('classifies underweight BMI', () {
      final result = BmiCalculator.calculate(weightKg: 40, heightCm: 170);
      expect(result!.category, BmiCategory.underweight);
    });

    test('classifies overweight BMI', () {
      final result = BmiCalculator.calculate(weightKg: 70, heightCm: 160);
      expect(result!.category, BmiCategory.overweight);
    });

    test('classifies obese BMI', () {
      final result = BmiCalculator.calculate(weightKg: 90, heightCm: 160);
      expect(result!.category, BmiCategory.obese);
    });
  });
}
