import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/features/deteksi_dini/domain/nutritional_status_calculator.dart';
import 'package:peka_stunting/features/deteksi_dini/domain/permenkes_z_score.dart';

void main() {
  group('Permenkes z-score', () {
    test('below median uses minus 1 SD denominator', () {
      // Contoh Permenkes: BB 13 kg, median 14.3, -1 SD 12.7
      final z = permenkesZScore(
        value: 13.0,
        median: 14.3,
        minus1Sd: 12.7,
        plus1Sd: 16.2,
      );

      expect(z, closeTo(-0.81, 0.01));
    });

    test('above median uses plus 1 SD denominator', () {
      final z = permenkesZScore(
        value: 15.5,
        median: 14.3,
        minus1Sd: 12.7,
        plus1Sd: 16.2,
      );

      expect(z, closeTo(0.63, 0.01));
    });
  });

  group('NutritionalStatusCalculator', () {
    test('returns three indicators for valid input', () {
      final result = NutritionalStatusCalculator.calculate(
        NutritionalStatusInput(
          birthDate: DateTime(2024, 1, 15),
          gender: 'L',
          weightKg: 10.5,
          heightCm: 84.0,
          referenceDate: DateTime(2026, 1, 15),
        ),
      );

      expect(result, isNotNull);
      expect(result!.ageMonths, 24);
      expect(result.heightForAge.indicatorLabel, 'Tinggi Badan/Umur');
      expect(result.weightForAge.indicatorLabel, 'Berat Badan/Umur');
      expect(result.weightForHeight.indicatorLabel, 'Tinggi/Berat Badan');
    });
  });
}
