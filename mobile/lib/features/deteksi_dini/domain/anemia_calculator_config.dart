import 'anemia_risk_calculator.dart';

/// Konfigurasi kuesioner anemia dari API (`calculator_config`).
class AnemiaCalculatorConfig {
  const AnemiaCalculatorConfig({
    required this.riskYesThreshold,
    required this.questions,
  });

  final int riskYesThreshold;
  final List<AnemiaScreeningQuestion> questions;

  static AnemiaCalculatorConfig? fromJson(Map<String, dynamic>? json) {
    if (json == null || json.isEmpty) {
      return null;
    }

    final threshold = json['risk_yes_threshold'];
    final rawQuestions = json['questions'];

    if (rawQuestions is! List || rawQuestions.isEmpty) {
      return null;
    }

    final questions = <AnemiaScreeningQuestion>[];
    for (final entry in rawQuestions) {
      if (entry is! Map) {
        continue;
      }
      final map = Map<String, dynamic>.from(entry);
      final id = map['id']?.toString().trim();
      final text = map['text']?.toString().trim();
      if (id == null || id.isEmpty || text == null || text.isEmpty) {
        continue;
      }
      questions.add(AnemiaScreeningQuestion(id: id, text: text));
    }

    if (questions.isEmpty) {
      return null;
    }

    final parsedThreshold = threshold is int
        ? threshold
        : int.tryParse(threshold?.toString() ?? '');

    return AnemiaCalculatorConfig(
      riskYesThreshold: parsedThreshold ?? AnemiaRiskCalculator.defaultRiskYesThreshold,
      questions: questions,
    );
  }
}
