import 'bmi_calculator.dart';

class BreastfeedingScreeningQuestion {
  const BreastfeedingScreeningQuestion({
    required this.id,
    required this.text,
  });

  final String id;
  final String text;
}

class BreastfeedingSuccessResult {
  const BreastfeedingSuccessResult({
    required this.yesCount,
    required this.totalQuestions,
  });

  final int yesCount;
  final int totalQuestions;
}

class BreastfeedingSuccessCalculator {
  /// Skor 8–10 = Menyusui Berhasil; skor < 8 = Perlu Evaluasi dan Dukungan.
  static const int successThreshold = 8;

  static const List<BreastfeedingScreeningQuestion> defaultQuestions = [
    BreastfeedingScreeningQuestion(
      id: 'feeding_frequency',
      text: 'Apakah bayi menyusu minimal 8-12 kali dalam 24 jam?',
    ),
    BreastfeedingScreeningQuestion(
      id: 'position_latch',
      text: 'Apakah bayi menyusu dengan posisi dan perlekatan yang benar?',
    ),
    BreastfeedingScreeningQuestion(
      id: 'swallowing_sound',
      text: 'Apakah terdengar suara menelan saat bayi menyusu?',
    ),
    BreastfeedingScreeningQuestion(
      id: 'softer_breast',
      text: 'Apakah payudara terasa lebih lunak setelah menyusui?',
    ),
    BreastfeedingScreeningQuestion(
      id: 'satisfied_calm',
      text: 'Apakah bayi tampak puas dan tenang setelah menyusui?',
    ),
    BreastfeedingScreeningQuestion(
      id: 'wet_diapers',
      text:
          'Apakah bayi BAK minimal 6 kali dalam 24 jam (setelah usia 5 hari)?',
    ),
    BreastfeedingScreeningQuestion(
      id: 'clear_urine',
      text: 'Apakah warna urin bayi jernih (tidak kuning pekat)?',
    ),
    BreastfeedingScreeningQuestion(
      id: 'bowel_movements',
      text:
          'Apakah bayi BAB minimal 3-4 kali dalam 24 jam dengan tekstur lembek/cair kekuningan (setelah usia 4 hari)?',
    ),
    BreastfeedingScreeningQuestion(
      id: 'birth_weight_regained',
      text:
          'Apakah berat badan bayi kembali ke berat lahir pada usia 10-14 hari?',
    ),
    BreastfeedingScreeningQuestion(
      id: 'weight_gain_curve',
      text: 'Apakah kenaikan berat badan bayi sesuai dengan kurva pertumbuhan?',
    ),
  ];

  static BreastfeedingSuccessResult? calculate({
    required List<BreastfeedingScreeningQuestion> questions,
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

    return BreastfeedingSuccessResult(
      yesCount: yesCount,
      totalQuestions: questions.length,
    );
  }

  static String fallbackRecommendation(int yesCount) {
    if (yesCount >= successThreshold) {
      return 'Selamat! Indikator keberhasilan menyusui Anda baik. Pertahankan ASI '
          'eksklusif, susui sesuai kebutuhan bayi, dan pastikan ibu juga cukup makan dan minum.';
    }
    return 'Beberapa indikator keberhasilan menyusui belum terpenuhi. Segera konsultasikan '
        'ke Puskesmas atau fasilitas kesehatan untuk evaluasi dan bimbingan laktasi.';
  }

  static ColorHint fallbackColorHint(int yesCount) {
    if (yesCount >= successThreshold) {
      return ColorHint.success;
    }
    return ColorHint.warning;
  }

  static String fallbackCategoryLabel(int yesCount) {
    if (yesCount >= successThreshold) {
      return 'Menyusui Berhasil';
    }
    return 'Perlu Evaluasi dan Dukungan Menyusui';
  }
}
