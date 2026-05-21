import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/splash_repository.dart';

final splashImageUrlProvider = FutureProvider<String?>((ref) {
  return ref.read(splashRepositoryProvider).fetchSplashImageUrl();
});
