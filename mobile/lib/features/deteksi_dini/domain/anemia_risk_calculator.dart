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

class AnemiaRiskResult {
  const AnemiaRiskResult({
    required this.yesCount,
    required this.totalQuestions,
  });

  final int yesCount;
  final int totalQuestions;
}

class AnemiaRiskCalculator {
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
    );
  }

  /// Teks anjuran fallback bila rules CMS belum tersedia di cache.
  static String fallbackRecommendation(int yesCount) {
    if (yesCount == 0) {
      return 'Selamat kondisi Anda normal. Langkah selanjutnya adalah mempertahankan '
          'kondisi tersebut agar cadangan zat besi dalam tubuh tetap terjaga selama '
          'masa pertumbuhan.';
    }
    if (yesCount > 7) {
      return 'Segera ke Puskesmas atau Fasilitas Kesehatan lainnya: Status risiko '
          'tinggi memerlukan pemeriksaan laboratorium untuk memastikan kadar Hb.';
    }
    if (yesCount >= 4) {
      return 'Perkuat asupan zat besi, hindari teh/kopi saat makan, konsumsi buah '
          'kaya vitamin C, dan rutin minum TTD 1 tablet seminggu sekali. Lakukan '
          'pemeriksaan Hb di fasilitas kesehatan bila keluhan berlanjut.';
    }
    return 'Perkuat asupan zat besi dengan lauk hewani dan sayuran hijau setiap hari.';
  }

  static ColorHint fallbackColorHint(int yesCount) {
    if (yesCount == 0) {
      return ColorHint.success;
    }
    if (yesCount > 7) {
      return ColorHint.danger;
    }
    if (yesCount >= 4) {
      return ColorHint.warning;
    }
    return ColorHint.warning;
  }

  static String fallbackCategoryLabel(int yesCount) {
    if (yesCount == 0) {
      return 'Tidak ada resiko Anemia';
    }
    if (yesCount > 7) {
      return 'Resiko Tinggi Anemia';
    }
    if (yesCount >= 4) {
      return 'Risiko Sedang Anemia';
    }
    return 'Risiko Rendah Anemia';
  }
}
