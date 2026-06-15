import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/features/deteksi_dini/domain/lila_calculator.dart';

void main() {
  group('LilaCalculator', () {
    test('returns null for invalid circumference', () {
      expect(
        LilaCalculator.calculate(
          circumferenceCm: 0,
          menuSlug: 'remaja-putri',
          ageYears: 12,
        ),
        isNull,
      );
      expect(
        LilaCalculator.calculate(
          circumferenceCm: 61,
          menuSlug: 'remaja-putri',
          ageYears: 12,
        ),
        isNull,
      );
    });

    test('returns null for remaja putri below minimum age', () {
      expect(
        LilaCalculator.calculate(
          circumferenceCm: 20,
          menuSlug: 'remaja-putri',
          ageYears: 9,
        ),
        isNull,
      );
    });

    group('remaja putri age 10-14', () {
      test('classifies at risk below 18.5 cm', () {
        final result = LilaCalculator.calculate(
          circumferenceCm: 18.4,
          menuSlug: 'remaja-putri',
          ageYears: 12,
        );
        expect(result!.category, LilaCategory.atRisk);
        expect(result.ageBand, LilaAgeBand.age10To14);
      });

      test('classifies normal at 18.5 cm', () {
        final result = LilaCalculator.calculate(
          circumferenceCm: 18.5,
          menuSlug: 'remaja-putri',
          ageYears: 12,
        );
        expect(result!.category, LilaCategory.normal);
      });
    });

    group('remaja putri age 15-17', () {
      test('classifies at risk below 22 cm', () {
        final result = LilaCalculator.calculate(
          circumferenceCm: 21.9,
          menuSlug: 'remaja-putri',
          ageYears: 16,
        );
        expect(result!.category, LilaCategory.atRisk);
        expect(result.ageBand, LilaAgeBand.age15To17);
      });

      test('classifies normal at 22 cm', () {
        final result = LilaCalculator.calculate(
          circumferenceCm: 22,
          menuSlug: 'remaja-putri',
          ageYears: 16,
        );
        expect(result!.category, LilaCategory.normal);
      });
    });

    group('remaja putri age over 17', () {
      test('classifies at risk below 23.5 cm', () {
        final result = LilaCalculator.calculate(
          circumferenceCm: 23.4,
          menuSlug: 'remaja-putri',
          ageYears: 18,
        );
        expect(result!.category, LilaCategory.atRisk);
        expect(result.ageBand, LilaAgeBand.ageOver17);
      });

      test('classifies normal at 23.5 cm', () {
        final result = LilaCalculator.calculate(
          circumferenceCm: 23.5,
          menuSlug: 'remaja-putri',
          ageYears: 18,
        );
        expect(result!.category, LilaCategory.normal);
      });
    });

    group('other menus use flat 23.5 cm threshold', () {
      test('classifies normal above 23.5 cm regardless of age', () {
        final result = LilaCalculator.calculate(
          circumferenceCm: 24,
          menuSlug: 'ibu-hamil',
          ageYears: 25,
        );
        expect(result!.category, LilaCategory.normal);
        expect(result.ageBand, isNull);
      });

      test('classifies at risk below 23.5 cm', () {
        final result = LilaCalculator.calculate(
          circumferenceCm: 22,
          menuSlug: 'ibu-hamil',
          ageYears: 25,
        );
        expect(result!.category, LilaCategory.atRisk);
      });
    });
  });
}
