import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/features/deteksi_dini/domain/breastfeeding_calculator_config.dart';
import 'package:peka_stunting/features/deteksi_dini/domain/breastfeeding_success_calculator.dart';

void main() {
  group('BreastfeedingSuccessCalculator', () {
    test('calculate returns yes count from answers', () {
      final questions = BreastfeedingSuccessCalculator.defaultQuestions;
      final answers = {
        for (final q in questions)
          q.id: q.id == 'feeding_frequency' || q.id == 'wet_diapers',
      };

      final result = BreastfeedingSuccessCalculator.calculate(
        questions: questions,
        answers: answers,
      );

      expect(result, isNotNull);
      expect(result!.yesCount, 2);
      expect(result.totalQuestions, questions.length);
    });

    test('calculate returns null when answers incomplete', () {
      final result = BreastfeedingSuccessCalculator.calculate(
        questions: BreastfeedingSuccessCalculator.defaultQuestions,
        answers: const {'good_latch': true},
      );

      expect(result, isNull);
    });
  });

  group('BreastfeedingCalculatorConfig', () {
    test('fromJson parses calculator_config from API', () {
      final config = BreastfeedingCalculatorConfig.fromJson({
        'risk_yes_threshold': 8,
        'questions': [
          {'id': 'feeding_frequency', 'text': 'Pertanyaan uji?'},
        ],
      });

      expect(config, isNotNull);
      expect(config!.riskYesThreshold, 8);
      expect(config.questions, hasLength(1));
      expect(config.questions.first.id, 'feeding_frequency');
    });
  });
}
