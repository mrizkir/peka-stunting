/// Tabel acuan Permenkes No.2 Tahun 2020: [median, -1 SD, +1 SD].
/// Nilai disimpan statis di aplikasi — tidak perlu fetch eksternal.
class PermenkesReferenceTables {
  PermenkesReferenceTables._();

  static List<double>? heightForAge(int ageMonths, String gender) {
    return _byAge(gender == 'L' ? _hfaBoys : _hfaGirls, ageMonths);
  }

  static List<double>? weightForAge(int ageMonths, String gender) {
    return _byAge(gender == 'L' ? _wfaBoys : _wfaGirls, ageMonths);
  }

  static List<double>? weightForHeight(double heightCm, String gender) {
    return _byHeight(gender == 'L' ? _wfhBoys : _wfhGirls, heightCm);
  }

  static List<double>? _byAge(Map<int, List<double>> table, int ageMonths) {
    final month = ageMonths.clamp(0, 60);
    if (table.containsKey(month)) return table[month];

    final keys = table.keys.toList()..sort();
    var lower = keys.first;
    for (final key in keys) {
      if (key <= month) lower = key;
      if (key >= month) {
        final upper = key;
        if (lower == upper) return table[lower];
        final t = (month - lower) / (upper - lower);
        return _lerp(table[lower]!, table[upper]!, t);
      }
    }
    return table[lower];
  }

  static List<double>? _byHeight(
    Map<int, List<double>> table,
    double heightCm,
  ) {
    final height = heightCm.round();
    if (table.containsKey(height)) return table[height];

    final keys = table.keys.toList()..sort();
    if (keys.isEmpty) return null;
    if (height <= keys.first) return table[keys.first];
    if (height >= keys.last) return table[keys.last];

    var lower = keys.first;
    for (final key in keys) {
      if (key <= height) lower = key;
      if (key >= height) {
        final upper = key;
        if (lower == upper) return table[lower];
        final t = (height - lower) / (upper - lower);
        return _lerp(table[lower]!, table[upper]!, t);
      }
    }
    return table[lower];
  }

  static List<double> _lerp(List<double> a, List<double> b, double t) {
    return [
      a[0] + (b[0] - a[0]) * t,
      a[1] + (b[1] - a[1]) * t,
      a[2] + (b[2] - a[2]) * t,
    ];
  }

  // Titik acuan umur 0–60 bulan (median, -1 SD, +1 SD).
  static const _hfaBoys = {
    0: [49.88, 48.12, 51.64],
    6: [67.62, 64.94, 70.30],
    12: [75.75, 73.00, 78.50],
    24: [87.12, 84.06, 90.17],
    36: [96.09, 92.70, 99.48],
    48: [103.33, 99.68, 106.98],
    60: [109.96, 105.92, 114.00],
  };

  static const _hfaGirls = {
    0: [49.15, 47.36, 50.94],
    6: [65.73, 63.08, 68.38],
    12: [74.00, 71.18, 76.82],
    24: [86.39, 83.27, 89.51],
    36: [95.05, 91.53, 98.57],
    48: [102.73, 98.90, 106.56],
    60: [109.42, 105.28, 113.56],
  };

  static const _wfaBoys = {
    0: [3.35, 2.76, 3.97],
    6: [7.93, 7.08, 8.82],
    12: [9.65, 8.62, 10.74],
    24: [12.15, 10.84, 13.62],
    36: [14.30, 12.70, 16.20],
    48: [16.32, 14.50, 18.37],
    60: [18.34, 16.32, 20.61],
  };

  static const _wfaGirls = {
    0: [3.23, 2.65, 3.85],
    6: [7.30, 6.48, 8.16],
    12: [8.95, 7.97, 9.98],
    24: [11.54, 10.23, 12.94],
    36: [13.76, 12.13, 15.48],
    48: [15.66, 13.79, 17.66],
    60: [17.60, 15.52, 19.79],
  };

  // Berat menurut tinggi badan (BB/TB) — acuan per cm.
  static const _wfhBoys = {
    45: [2.52, 2.22, 2.86],
    55: [4.82, 4.28, 5.42],
    65: [7.41, 6.68, 8.20],
    75: [9.65, 8.62, 10.74],
    85: [11.79, 10.44, 13.18],
    95: [13.70, 12.08, 15.35],
    105: [15.45, 13.58, 17.35],
    115: [17.10, 15.00, 19.20],
  };

  static const _wfhGirls = {
    45: [2.46, 2.17, 2.78],
    55: [4.65, 4.13, 5.22],
    65: [7.04, 6.35, 7.78],
    75: [8.95, 7.97, 9.98],
    85: [10.93, 9.67, 12.24],
    95: [12.74, 11.22, 14.28],
    105: [14.42, 12.65, 16.20],
    115: [16.02, 14.00, 18.05],
  };
}
