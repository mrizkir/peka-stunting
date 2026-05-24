import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/features/deteksi_dini/domain/anemia_risk_calculator.dart';

final _questions = AnemiaRiskCalculator.defaultQuestions;

Map<String, bool> _allNo() => {
      for (final q in _questions) q.id: false,
    };

Map<String, bool> _withYes(List<String> ids) {
  final answers = _allNo();
  for (final id in ids) {
    answers[id] = true;
  }
  return answers;
}

void main() {
  group('AnemiaRiskCalculator', () {
    test('returns null when answers incomplete', () {
      expect(
        AnemiaRiskCalculator.calculate(
          questions: _questions,
          answers: {'fatigue_5l': true},
        ),
        isNull,
      );
    });

    test('classifies low risk when fewer than 3 yes answers', () {
      final result = AnemiaRiskCalculator.calculate(
        questions: _questions,
        answers: _withYes(['fatigue_5l', 'dizziness_headache']),
      );
      expect(result, isNotNull);
      expect(result!.yesCount, 2);
      expect(result.category, AnemiaRiskCategory.lowRisk);
      expect(result.categoryLabel, 'Risiko anemia relatif rendah');
    });

    test('classifies at risk when 3 or more yes answers', () {
      final result = AnemiaRiskCalculator.calculate(
        questions: _questions,
        answers: _withYes(['fatigue_5l', 'dizziness_headache', 'concentration']),
      );
      expect(result!.category, AnemiaRiskCategory.atRisk);
      expect(result.categoryLabel, 'Anda berisiko mengalami anemia');
    });

    test('classifies at risk when all answers are yes', () {
      final result = AnemiaRiskCalculator.calculate(
        questions: _questions,
        answers: {
          for (final q in _questions) q.id: true,
        },
      );
      expect(result!.yesCount, 14);
      expect(result.category, AnemiaRiskCategory.atRisk);
    });

    test('respects custom risk threshold from backend config', () {
      final result = AnemiaRiskCalculator.calculate(
        questions: _questions,
        answers: _withYes(['fatigue_5l', 'dizziness_headache']),
        riskYesThreshold: 2,
      );
      expect(result!.category, AnemiaRiskCategory.atRisk);
    });
  });
}
