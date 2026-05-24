class AppConfig {
  static const String productionApiBaseUrl =
      'https://peka-stunting.yacanet.com/api/v1';

  /// Override untuk development lokal: `flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1`
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: productionApiBaseUrl,
  );

  static const String appName = 'PEKA Stunting';

  static const String logoAssetPath = 'assets/images/logo_app_1.png';

  static const String appTagline =
      'Paket Edukasi Komprehensif Berbasis Android dan '
      'Kearifan Lokal untuk Deteksi Dini dan Pencegahan Stunting';
}
