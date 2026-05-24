import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/features/deteksi_dini/domain/lila_calculator.dart';

void main() {
  group('LilaCalculator', () {
    test('returns null for invalid input', () {
      expect(LilaCalculator.calculate(circumferenceCm: 0), isNull);
      expect(LilaCalculator.calculate(circumferenceCm: 61), isNull);
    });

    test('classifies at risk when LILA below 23.5 cm', () {
      final result = LilaCalculator.calculate(circumferenceCm: 22);
      expect(result, isNotNull);
      expect(result!.valueCm, 22);
      expect(result.category, LilaCategory.atRisk);
      expect(result.categoryLabel, 'Anda berisiko kekurangan gizi (KEK)');
    });

    test('classifies normal when LILA at 23.5 cm', () {
      final result = LilaCalculator.calculate(circumferenceCm: 23.5);
      expect(result!.category, LilaCategory.normal);
      expect(result.categoryLabel, 'Selamat, status gizi relatif normal');
    });

    test('classifies normal when LILA above 23.5 cm', () {
      final result = LilaCalculator.calculate(circumferenceCm: 26.2);
      expect(result!.valueCm, 26.2);
      expect(result.category, LilaCategory.normal);
    });
  });
}
