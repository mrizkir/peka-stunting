import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/features/deteksi_dini/domain/anemia_calculator_config.dart';

void main() {
  test('parses questionnaire from calculator_config JSON', () {
    final config = AnemiaCalculatorConfig.fromJson({
      'risk_yes_threshold': 4,
      'questions': [
        {'id': 'fatigue_5l', 'text': 'Sering lelah?'},
        {'id': 'pale', 'text': 'Tampak pucat?'},
      ],
    });

    expect(config, isNotNull);
    expect(config!.riskYesThreshold, 4);
    expect(config.questions, hasLength(2));
    expect(config.questions.first.id, 'fatigue_5l');
  });

  test('returns null for invalid config', () {
    expect(AnemiaCalculatorConfig.fromJson(null), isNull);
    expect(AnemiaCalculatorConfig.fromJson({'questions': []}), isNull);
  });
}
