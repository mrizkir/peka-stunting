import 'permenkes_reference_tables.dart';
import 'permenkes_z_score.dart';

class NutritionalStatusInput {
  const NutritionalStatusInput({
    required this.birthDate,
    required this.gender,
    required this.weightKg,
    required this.heightCm,
    this.referenceDate,
  });

  final DateTime birthDate;
  final String gender;
  final double weightKg;
  final double heightCm;
  final DateTime? referenceDate;
}

class NutritionalStatusResult {
  const NutritionalStatusResult({
    required this.ageMonths,
    required this.heightForAge,
    required this.weightForAge,
    required this.weightForHeight,
  });

  final int ageMonths;
  final ZScoreAssessment heightForAge;
  final ZScoreAssessment weightForAge;
  final ZScoreAssessment weightForHeight;
}

class NutritionalStatusCalculator {
  static NutritionalStatusResult? calculate(NutritionalStatusInput input) {
    if (input.gender != 'L' && input.gender != 'P') {
      return null;
    }
    if (input.weightKg <= 0 || input.heightCm <= 0) {
      return null;
    }

    final today = input.referenceDate ?? DateTime.now();
    final ageMonths = _ageInMonths(input.birthDate, today);
    if (ageMonths < 0 || ageMonths > 60) {
      return null;
    }

    final hfaRef = PermenkesReferenceTables.heightForAge(ageMonths, input.gender);
    final wfaRef = PermenkesReferenceTables.weightForAge(ageMonths, input.gender);
    final wfhRef =
        PermenkesReferenceTables.weightForHeight(input.heightCm, input.gender);

    if (hfaRef == null || wfaRef == null || wfhRef == null) {
      return null;
    }

    return NutritionalStatusResult(
      ageMonths: ageMonths,
      heightForAge: assess(
        indicator: ZScoreIndicator.heightForAge,
        value: input.heightCm,
        median: hfaRef[0],
        minus1Sd: hfaRef[1],
        plus1Sd: hfaRef[2],
      ),
      weightForAge: assess(
        indicator: ZScoreIndicator.weightForAge,
        value: input.weightKg,
        median: wfaRef[0],
        minus1Sd: wfaRef[1],
        plus1Sd: wfaRef[2],
      ),
      weightForHeight: assess(
        indicator: ZScoreIndicator.weightForHeight,
        value: input.weightKg,
        median: wfhRef[0],
        minus1Sd: wfhRef[1],
        plus1Sd: wfhRef[2],
      ),
    );
  }

  static int ageInMonths(DateTime birthDate, [DateTime? referenceDate]) {
    final today = referenceDate ?? DateTime.now();
    var months =
        (today.year - birthDate.year) * 12 + (today.month - birthDate.month);
    if (today.day < birthDate.day) {
      months -= 1;
    }
    return months;
  }

  static int _ageInMonths(DateTime birthDate, DateTime today) =>
      ageInMonths(birthDate, today);
}
