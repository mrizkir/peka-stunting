/// Kategori IMT untuk remaja (standar umum).
enum BmiCategory {
  underweight,
  normal,
  overweight,
  obese,
}

class BmiResult {
  const BmiResult({
    required this.value,
    required this.category,
  });

  final double value;
  final BmiCategory category;

  String get categoryLabel {
    switch (category) {
      case BmiCategory.underweight:
        return 'Kurus';
      case BmiCategory.normal:
        return 'Normal';
      case BmiCategory.overweight:
        return 'Gemuk';
      case BmiCategory.obese:
        return 'Obesitas';
    }
  }

  String get categoryDescription {
    switch (category) {
      case BmiCategory.underweight:
        return 'Bagi remaja dengan kategori kurus (IMT di bawah 18,5), langkah utama yang dianjurkan adalah meningkatkan asupan kalori dan nutrisi secara sehat, kebiasaan minum yang tepat, tidur yang teratur, melakukan latihan fisik untuk membangun massa otot dan cek rutin IMT secara berkala ya. Lakukan konsultasi kesehatan di Puskesmas atau fasilitas kesehatan lainnya.';
      case BmiCategory.normal:
        return 'Selamat anda dalam kondisi Normal. Bagi remaja dengan IMT normal (berada di rentang 18,5 hingga 25,0), fokus utamanya adalah pemeliharaan dan menjaga komposisi tubuh agar tetap sehat selama masa pertumbuhan dengan cara pertahankan kualitas nutrisi makanan, olahraga yang rutin, pastikan minum air putih 2 liter per hari, tidur 8 – 10 jam/per hari, dan cek rutin secara berkala ya.';
      case BmiCategory.overweight:
        return 'Bagi remaja dengan IMT kategori gemuk (overweight), tujuannya bukan sekadar menurunkan berat badan secara drastis , melainkan memperlambat laju kenaikan berat badan sambil membiarkan tinggi badan bertambah, serta memperbaiki pola hidup yang lebih baik seperti tidur yang teratur, pastikan minum air putih 2 liter per hari, tidur 8 – 10 jam/per hari, tingkatkan aktivitas fisik dan cek rutin IMT secara berkala ya';
      case BmiCategory.obese:
        return 'Bagi remaja dengan IMT kategori obesitas (IMT 30 atau lebih), tujuannya bukan sekadar menurunkan berat badan secara drastis , melainkan memperlambat laju kenaikan berat badan sambil membiarkan tinggi badan bertambah, serta memperbaiki pola hidup yang lebih baik seperti tidur yang teratur, pastikan minum air putih 2 liter per hari, tidur 8 – 10 jam/per hari, tingkatkan aktivitas fisik dan cek rutin IMT secara berkala ya';
    }
  }

  ColorHint get colorHint {
    switch (category) {
      case BmiCategory.underweight:
        return ColorHint.warning;
      case BmiCategory.normal:
        return ColorHint.success;
      case BmiCategory.overweight:
        return ColorHint.warning;
      case BmiCategory.obese:
        return ColorHint.danger;
    }
  }
}

enum ColorHint { success, warning, danger }

class BmiCalculator {
  /// Menghitung IMT: berat (kg) / tinggi (m)²
  static BmiResult? calculate({
    required double weightKg,
    required double heightCm,
  }) {
    if (weightKg <= 0 || heightCm <= 0) {
      return null;
    }

    final heightM = heightCm / 100;
    final bmi = weightKg / (heightM * heightM);
    final rounded = double.parse(bmi.toStringAsFixed(1));

    return BmiResult(
      value: rounded,
      category: _categoryFor(rounded),
    );
  }

  static BmiCategory _categoryFor(double bmi) {
    if (bmi < 18.5) {
      return BmiCategory.underweight;
    }
    if (bmi < 25) {
      return BmiCategory.normal;
    }
    if (bmi < 30) {
      return BmiCategory.overweight;
    }
    return BmiCategory.obese;
  }
}
