class AppConfig {
  /// Emulator Android → host machine Laravel (`php artisan serve`).
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api/v1',
  );

  static const String appName = 'PEKA Stunting';
}
