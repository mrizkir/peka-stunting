import 'bmi_calculator.dart';

/// Rumus Permenkes No.2 Tahun 2020:
/// - jika nilai < median: (nilai - median) / (median - (-1 SD))
/// - jika nilai > median: (nilai - median) / ((+1 SD) - median)
double permenkesZScore({
  required double value,
  required double median,
  required double minus1Sd,
  required double plus1Sd,
}) {
  if (value < median) {
    final denominator = median - minus1Sd;
    if (denominator == 0) return 0;
    return (value - median) / denominator;
  }
  if (value > median) {
    final denominator = plus1Sd - median;
    if (denominator == 0) return 0;
    return (value - median) / denominator;
  }
  return 0;
}

enum ZScoreIndicator {
  heightForAge,
  weightForAge,
  weightForHeight,
}

class ZScoreAssessment {
  const ZScoreAssessment({
    required this.indicator,
    required this.zScore,
    required this.categoryLabel,
  });

  final ZScoreIndicator indicator;
  final double zScore;
  final String categoryLabel;

  String get indicatorLabel {
    switch (indicator) {
      case ZScoreIndicator.heightForAge:
        return 'Tinggi Badan/Umur';
      case ZScoreIndicator.weightForAge:
        return 'Berat Badan/Umur';
      case ZScoreIndicator.weightForHeight:
        return 'Tinggi/Berat Badan';
    }
  }

  ColorHint get colorHint {
    final z = zScore;
    switch (indicator) {
      case ZScoreIndicator.heightForAge:
        if (z < -2) return ColorHint.danger;
        if (z < -1) return ColorHint.warning;
        return ColorHint.success;
      case ZScoreIndicator.weightForAge:
        if (z < -2) return ColorHint.danger;
        if (z > 1) return ColorHint.warning;
        return ColorHint.success;
      case ZScoreIndicator.weightForHeight:
        if (z < -2) return ColorHint.danger;
        if (z > 1) return ColorHint.warning;
        if (z > 2) return ColorHint.danger;
        return ColorHint.success;
    }
  }
}

String categorizeHeightForAge(double z) {
  if (z < -3) return 'Sangat pendek';
  if (z < -2) return 'Pendek (stunting)';
  if (z <= 3) return 'Normal';
  return 'Tinggi';
}

String categorizeWeightForAge(double z) {
  if (z < -3) return 'Berat badan sangat kurang';
  if (z < -2) return 'Berat badan kurang';
  if (z <= 1) return 'Berat badan normal';
  return 'Risiko berat badan lebih';
}

String categorizeWeightForHeight(double z) {
  if (z < -3) return 'Gizi buruk';
  if (z < -2) return 'Gizi kurang';
  if (z <= 1) return 'Gizi baik';
  if (z <= 2) return 'Berisiko gizi lebih';
  if (z <= 3) return 'Gizi lebih';
  return 'Obesitas';
}

ZScoreAssessment assess({
  required ZScoreIndicator indicator,
  required double value,
  required double median,
  required double minus1Sd,
  required double plus1Sd,
}) {
  final z = permenkesZScore(
    value: value,
    median: median,
    minus1Sd: minus1Sd,
    plus1Sd: plus1Sd,
  );
  final rounded = double.parse(z.toStringAsFixed(2));

  final label = switch (indicator) {
    ZScoreIndicator.heightForAge => categorizeHeightForAge(rounded),
    ZScoreIndicator.weightForAge => categorizeWeightForAge(rounded),
    ZScoreIndicator.weightForHeight => categorizeWeightForHeight(rounded),
  };

  return ZScoreAssessment(
    indicator: indicator,
    zScore: rounded,
    categoryLabel: label,
  );
}
