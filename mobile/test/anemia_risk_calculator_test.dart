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

    test('counts yes answers', () {
      final result = AnemiaRiskCalculator.calculate(
        questions: _questions,
        answers: _withYes(['fatigue_5l', 'dizziness_headache']),
      );
      expect(result, isNotNull);
      expect(result!.yesCount, 2);
      expect(result.totalQuestions, 14);
    });

    test('counts all yes answers', () {
      final result = AnemiaRiskCalculator.calculate(
        questions: _questions,
        answers: {
          for (final q in _questions) q.id: true,
        },
      );
      expect(result!.yesCount, 14);
    });
  });
}
