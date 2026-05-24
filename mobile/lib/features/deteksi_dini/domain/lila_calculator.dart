import 'bmi_calculator.dart';

/// Kategori LILA (Lingkar Lengan Atas) — batas 23,5 cm (standar skrining KEK).
enum LilaCategory {
  atRisk,
  normal,
}

class LilaResult {
  const LilaResult({
    required this.valueCm,
    required this.category,
  });

  final double valueCm;
  final LilaCategory category;

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
        return 'Kekurangan Energi Kronis (KEK) pada remaja, yang biasanya '
            'ditandai dengan ukuran Lingkar Lengan Atas (LiLA) kurang dari '
            '23,5 cm, merupakan kondisi serius karena menunjukkan kekurangan '
            'gizi jangka panjang yang bisa menghambat pertumbuhan dan '
            'menurunkan sistem imun. Terus apa yang harus dilakukan? Remaja '
            'harus meningkatkan konsumsi protein kualitas tinggi seperti telur, '
            'ikan, ayam, daging, dan susu untuk memperbaiki jaringan tubuh dan '
            'meningkatkan massa otot. Selain makan besar 3 kali sehari, sangat '
            'disarankan mengonsumsi makanan tambahan padat gizi (seperti '
            'biskuit khusus dari puskesmas, kacang hijau, atau telur rebus) '
            'di antara waktu makan. Lakukan konsultasi kesehatan di Puskesmas '
            'atau fasilitas kesehatan lainnya.';
      case LilaCategory.normal:
        return 'Jika hasil Lingkar Lengan Atas (LiLA) remaja normal '
            '(≥ 23,5 cm), artinya cadangan lemak dan massa otot tubuh saat ini '
            'dalam kondisi cukup. Fokus utamanya adalah menjaga agar status '
            'gizi tetap stabil dan tidak jatuh ke risiko KEK atau sebaliknya '
            'menjadi obesitas. Pertahankan pola makan gizi seimbang dan '
            'aktifitas fisik secara rutin.';
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
}

class LilaCalculator {
  /// Batas LILA normal menurut standar skrining KEK (cm).
  static const double normalMinimumCm = 23.5;

  static LilaResult? calculate({required double circumferenceCm}) {
    if (circumferenceCm <= 0 || circumferenceCm > 60) {
      return null;
    }

    final rounded = double.parse(circumferenceCm.toStringAsFixed(1));

    return LilaResult(
      valueCm: rounded,
      category: rounded < normalMinimumCm
          ? LilaCategory.atRisk
          : LilaCategory.normal,
    );
  }
}
