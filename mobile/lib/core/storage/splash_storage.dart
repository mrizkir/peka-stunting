import 'package:shared_preferences/shared_preferences.dart';

class SplashStorage {
  static const _urlKey = 'splash_image_url';

  Future<void> saveCachedUrl(String url) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_urlKey, url.trim());
  }

  Future<String?> readCachedUrl() async {
    final prefs = await SharedPreferences.getInstance();
    final url = prefs.getString(_urlKey)?.trim();
    if (url == null || url.isEmpty) {
      return null;
    }
    return url;
  }

  Future<void> clearCachedUrl() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_urlKey);
  }
}
