import 'bmi_calculator.dart';

/// Pertanyaan skrining risiko anemia (gejala & faktor risiko remaja putri).
class AnemiaScreeningQuestion {
  const AnemiaScreeningQuestion({
    required this.id,
    required this.text,
  });

  final String id;
  final String text;
}

enum AnemiaRiskCategory {
  atRisk,
  lowRisk,
}

class AnemiaRiskResult {
  const AnemiaRiskResult({
    required this.yesCount,
    required this.totalQuestions,
    required this.category,
  });

  final int yesCount;
  final int totalQuestions;
  final AnemiaRiskCategory category;

  String get categoryLabel {
    switch (category) {
      case AnemiaRiskCategory.atRisk:
        return 'Anda berisiko mengalami anemia';
      case AnemiaRiskCategory.lowRisk:
        return 'Risiko anemia relatif rendah';
    }
  }

  String get recommendation {
    switch (category) {
      case AnemiaRiskCategory.atRisk:
        return 'Berdasarkan jawaban Anda, terdapat beberapa gejala atau '
            'faktor risiko anemia. Anemia dapat menghambat konsentrasi, '
            'menurunkan daya tahan tubuh, dan memengaruhi pertumbuhan. '
            'Segera lakukan pemeriksaan kadar hemoglobin (Hb) di Puskesmas '
            'atau fasilitas kesehatan. Minum Tablet Tambah Darah (TTD) '
            'secara rutin sesuai anjuran, perbanyak makanan sumber zat besi '
            '(daging, ikan, telur, hati, kacang-kacangan, sayur hijau), '
            'jangan melewatkan sarapan, dan konsultasikan keluhan Anda ke '
            'tenaga kesehatan.';
      case AnemiaRiskCategory.lowRisk:
        return 'Gejala dan faktor risiko anemia yang Anda laporkan masih '
            'terbatas. Pertahankan pola makan bergizi (termasuk protein '
            'dan zat besi), sarapan setiap hari, minum Tablet Tambah Darah '
            '(TTD) bila Anda remaja putri yang mendapat program TTD, '
            'aktivitas fisik rutin, dan pemeriksaan kesehatan berkala. '
            'Jika keluhan seperti mudah lelah atau pucat muncul, segera '
            'periksakan diri ke fasilitas kesehatan.';
    }
  }

  ColorHint get colorHint {
    switch (category) {
      case AnemiaRiskCategory.atRisk:
        return ColorHint.danger;
      case AnemiaRiskCategory.lowRisk:
        return ColorHint.success;
    }
  }
}

class AnemiaRiskCalculator {
  /// Jumlah jawaban "Ya" minimum untuk dikategorikan berisiko.
  static const int defaultRiskYesThreshold = 3;

  /// Fallback bila backend belum mengirim kuesioner.
  static const List<AnemiaScreeningQuestion> defaultQuestions = [
    AnemiaScreeningQuestion(
      id: 'fatigue_5l',
      text:
          'Apakah Anda sering merasa lelah, letih, lesu, lemah, lalai (5L)?',
    ),
    AnemiaScreeningQuestion(
      id: 'dizziness_headache',
      text:
          'Apakah Anda sering merasa pusing, sakit kepala, atau mata berkunang-kunang?',
    ),
    AnemiaScreeningQuestion(
      id: 'pale_skin',
      text:
          'Apakah kulit, telapak tangan, atau bagian dalam kelopak mata Anda terlihat pucat?',
    ),
    AnemiaScreeningQuestion(
      id: 'shortness_breath',
      text:
          'Apakah Anda sering merasa napas pendek atau sesak setelah aktivitas ringan?',
    ),
    AnemiaScreeningQuestion(
      id: 'heart_palpitation',
      text: 'Apakah Anda sering merasa jantung berdebar-debar?',
    ),
    AnemiaScreeningQuestion(
      id: 'concentration',
      text: 'Apakah Anda sering merasa sulit berkonsentrasi?',
    ),
    AnemiaScreeningQuestion(
      id: 'cold_hands_feet',
      text: 'Apakah Anda sering merasa kedinginan pada tangan atau kaki?',
    ),
    AnemiaScreeningQuestion(
      id: 'low_iron_food',
      text:
          'Apakah Anda jarang mengonsumsi sumber zat besi (daging merah, hati, ikan, sayuran hijau) dalam seminggu?',
    ),
    AnemiaScreeningQuestion(
      id: 'tea_coffee_with_meal',
      text:
          'Apakah Anda sering minum teh atau kopi saat atau segera setelah makan?',
    ),
    AnemiaScreeningQuestion(
      id: 'skip_breakfast',
      text: 'Apakah Anda melewatkan sarapan secara rutin?',
    ),
    AnemiaScreeningQuestion(
      id: 'strict_diet',
      text:
          'Apakah Anda sedang dalam program diet ketat (mengurangi porsi makan secara drastis)?',
    ),
    AnemiaScreeningQuestion(
      id: 'heavy_menstruation',
      text:
          'Apakah siklus menstruasi Anda teratur, tetapi darah yang keluar sangat banyak atau lama (> 7 hari)?',
    ),
    AnemiaScreeningQuestion(
      id: 'previous_anemia',
      text: 'Apakah Anda pernah didiagnosa anemia sebelumnya?',
    ),
    AnemiaScreeningQuestion(
      id: 'low_ttd',
      text:
          'Apakah Anda jarang/tidak pernah mengonsumsi Tablet Tambah Darah (TTD)?',
    ),
  ];

  /// [answers] harus memuat jawaban untuk setiap pertanyaan (true = Ya).
  static AnemiaRiskResult? calculate({
    required List<AnemiaScreeningQuestion> questions,
    required Map<String, bool> answers,
    int riskYesThreshold = defaultRiskYesThreshold,
  }) {
    if (questions.isEmpty || answers.length != questions.length) {
      return null;
    }

    for (final question in questions) {
      if (!answers.containsKey(question.id)) {
        return null;
      }
    }

    final yesCount = answers.values.where((v) => v).length;

    return AnemiaRiskResult(
      yesCount: yesCount,
      totalQuestions: questions.length,
      category: yesCount >= riskYesThreshold
          ? AnemiaRiskCategory.atRisk
          : AnemiaRiskCategory.lowRisk,
    );
  }
}
