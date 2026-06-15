import 'bmi_calculator.dart';

/// Kelompok usia remaja putri untuk ambang LILA.
enum LilaAgeBand {
  age10To14,
  age15To17,
  ageOver17,
}

/// Kategori LILA (Lingkar Lengan Atas) — ambang bervariasi per kelompok usia.
enum LilaCategory {
  atRisk,
  normal,
}

class LilaAgeBandHelper {
  LilaAgeBandHelper._();

  static const remajaPutriMenuSlug = 'remaja-putri';
  static const minRemajaPutriAgeYears = 10;

  static bool usesAgeBands(String menuSlug) =>
      menuSlug == remajaPutriMenuSlug;

  static LilaAgeBand? bandForAge(int ageYears) {
    if (ageYears >= 10 && ageYears <= 14) {
      return LilaAgeBand.age10To14;
    }
    if (ageYears >= 15 && ageYears <= 17) {
      return LilaAgeBand.age15To17;
    }
    if (ageYears > 17) {
      return LilaAgeBand.ageOver17;
    }
    return null;
  }

  static String? indicatorForAge(int ageYears) {
    return switch (bandForAge(ageYears)) {
      LilaAgeBand.age10To14 => 'age_10_14',
      LilaAgeBand.age15To17 => 'age_15_17',
      LilaAgeBand.ageOver17 => 'age_gt_17',
      null => null,
    };
  }

  static double normalMinimumCm(LilaAgeBand band) {
    return switch (band) {
      LilaAgeBand.age10To14 => 18.5,
      LilaAgeBand.age15To17 => 22,
      LilaAgeBand.ageOver17 => 23.5,
    };
  }

  static double? normalMinimumCmForAge(int ageYears) {
    final band = bandForAge(ageYears);
    if (band == null) {
      return null;
    }
    return normalMinimumCm(band);
  }
}

class LilaResult {
  const LilaResult({
    required this.valueCm,
    required this.category,
    this.ageBand,
  });

  final double valueCm;
  final LilaCategory category;
  final LilaAgeBand? ageBand;

  String get categoryLabel {
    switch (category) {
      case LilaCategory.atRisk:
        return 'Anda berisiko kekurangan gizi (KEK)';
      case LilaCategory.normal:
        return 'Selamat, status gizi relatif normal';
    }
  }

  String get recommendation {
    switch (category) {
      case LilaCategory.atRisk:
        return _atRiskRecommendation(ageBand);
      case LilaCategory.normal:
        return _normalRecommendation(ageBand);
    }
  }

  ColorHint get colorHint {
    switch (category) {
      case LilaCategory.atRisk:
        return ColorHint.danger;
      case LilaCategory.normal:
        return ColorHint.success;
    }
  }

  static String _normalRecommendation(LilaAgeBand? band) {
    return switch (band) {
      LilaAgeBand.age10To14 =>
        'Untuk remaja putri berusia 10 – 14 tahun, Jika hasil Lingkar Lengan Atas (LiLA) remaja normal (≥ 18,5 cm), artinya cadangan lemak dan massa otot tubuh saat ini dalam kondisi cukup. Fokus utamanya adalah menjaga agar status gizi tetap stabil dan tidak jatuh ke risiko KEK atau sebaliknya menjadi obesitas. Pertahankan pola makan gizi seimbang dan aktifitas fisik secara rutin.',
      LilaAgeBand.age15To17 =>
        'Untuk remaja putri berusia 15 – 17 tahun, Jika hasil Lingkar Lengan Atas (LiLA) remaja normal (≥ 22 cm), artinya cadangan lemak dan massa otot tubuh saat ini dalam kondisi cukup. Fokus utamanya adalah menjaga agar status gizi tetap stabil dan tidak jatuh ke risiko KEK atau sebaliknya menjadi obesitas. Pertahankan pola makan gizi seimbang dan aktifitas fisik secara rutin.',
      LilaAgeBand.ageOver17 =>
        'Jika Lingkar Lengan Atas (LiLA) remaja > 17 tahun menunjukan normal (≥ 23,5 cm), artinya cadangan lemak dan massa otot tubuh saat ini dalam kondisi cukup. Fokus utamanya adalah menjaga agar status gizi tetap stabil dan tidak jatuh ke risiko KEK atau sebaliknya menjadi obesitas. Pertahankan pola makan gizi seimbang dan aktifitas fisik secara rutin.',
      null =>
        'Jika hasil Lingkar Lengan Atas (LiLA) normal (≥ 23,5 cm), artinya cadangan lemak dan massa otot tubuh saat ini dalam kondisi cukup. Fokus utamanya adalah menjaga agar status gizi tetap stabil dan tidak jatuh ke risiko KEK atau sebaliknya menjadi obesitas. Pertahankan pola makan gizi seimbang dan aktifitas fisik secara rutin.',
    };
  }

  static String _atRiskRecommendation(LilaAgeBand? band) {
    return switch (band) {
      LilaAgeBand.age10To14 =>
        'Kekurangan Energi Kronis (KEK) pada remaja putri usia 10 -14 tahun, yang biasanya ditandai dengan ukuran Lingkar Lengan Atas (LiLA) kurang dari 18,5 cm, merupakan kondisi serius karena menunjukkan kekurangan gizi jangka panjang yang bisa menghambat pertumbuhan dan menurunkan sistem imun. Terus apa yang harus dilakukan? Remaja harus meningkatkan konsumsi protein kualitas tinggi seperti telur, ikan, ayam, daging, dan susu untuk memperbaiki jaringan tubuh dan meningkatkan massa otot. Selain makan besar 3 kali sehari, sangat disarankan mengonsumsi makanan tambahan padat gizi (seperti biskuit khusus dari puskesmas, kacang hijau, atau telur rebus) di antara waktu makan. Lakukan konsultasi kesehatan di Puskesmas atau fasilitas kesehatan lainnya.',
      LilaAgeBand.age15To17 =>
        'Kekurangan Energi Kronis (KEK) pada remaja putri usia 15 -17 tahun, yang biasanya ditandai dengan ukuran Lingkar Lengan Atas (LiLA) kurang dari 22 cm, merupakan kondisi serius karena menunjukkan kekurangan gizi jangka panjang yang bisa menghambat pertumbuhan dan menurunkan sistem imun. Terus apa yang harus dilakukan? Remaja harus meningkatkan konsumsi protein kualitas tinggi seperti telur, ikan, ayam, daging, dan susu untuk memperbaiki jaringan tubuh dan meningkatkan massa otot. Selain makan besar 3 kali sehari, sangat disarankan mengonsumsi makanan tambahan padat gizi (seperti biskuit khusus dari puskesmas, kacang hijau, atau telur rebus) di antara waktu makan. Lakukan konsultasi kesehatan di Puskesmas atau fasilitas kesehatan lainnya.',
      LilaAgeBand.ageOver17 =>
        'Kekurangan Energi Kronis (KEK) pada remaja usia  > 17 tahun, yang biasanya ditandai dengan ukuran Lingkar Lengan Atas (LiLA) kurang dari 23,5 cm, merupakan kondisi serius karena menunjukkan kekurangan gizi jangka panjang yang bisa menghambat pertumbuhan dan menurunkan sistem imun. Terus apa yang harus dilakukan? Remaja harus meningkatkan konsumsi protein kualitas tinggi seperti telur, ikan, ayam, daging, dan susu untuk memperbaiki jaringan tubuh dan meningkatkan massa otot. Selain makan besar 3 kali sehari, sangat disarankan mengonsumsi makanan tambahan padat gizi (seperti biskuit khusus dari puskesmas, kacang hijau, atau telur rebus) di antara waktu makan. Lakukan konsultasi kesehatan di Puskesmas atau fasilitas kesehatan lainnya.',
      null =>
        'Kekurangan Energi Kronis (KEK), yang biasanya ditandai dengan ukuran Lingkar Lengan Atas (LiLA) kurang dari 23,5 cm, merupakan kondisi serius karena menunjukkan kekurangan gizi jangka panjang yang bisa menghambat pertumbuhan dan menurunkan sistem imun. Terus apa yang harus dilakukan? Tingkatkan konsumsi protein kualitas tinggi seperti telur, ikan, ayam, daging, dan susu untuk memperbaiki jaringan tubuh dan meningkatkan massa otot. Selain makan besar 3 kali sehari, sangat disarankan mengonsumsi makanan tambahan padat gizi (seperti biskuit khusus dari puskesmas, kacang hijau, atau telur rebus) di antara waktu makan. Lakukan konsultasi kesehatan di Puskesmas atau fasilitas kesehatan lainnya.',
    };
  }
}

class LilaCalculator {
  /// Batas LILA normal untuk remaja > 17 tahun (cm).
  static const double normalMinimumCm = 23.5;

  static LilaResult? calculate({
    required double circumferenceCm,
    required String menuSlug,
    int? ageYears,
  }) {
    if (circumferenceCm <= 0 || circumferenceCm > 60) {
      return null;
    }

    final rounded = double.parse(circumferenceCm.toStringAsFixed(1));
    final LilaAgeBand? ageBand;
    final double threshold;

    if (LilaAgeBandHelper.usesAgeBands(menuSlug)) {
      if (ageYears == null) {
        return null;
      }
      ageBand = LilaAgeBandHelper.bandForAge(ageYears);
      if (ageBand == null) {
        return null;
      }
      threshold = LilaAgeBandHelper.normalMinimumCm(ageBand);
    } else {
      ageBand = null;
      threshold = normalMinimumCm;
    }

    return LilaResult(
      valueCm: rounded,
      category: rounded >= threshold
          ? LilaCategory.normal
          : LilaCategory.atRisk,
      ageBand: ageBand,
    );
  }
}
