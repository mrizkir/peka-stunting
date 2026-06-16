import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/splash_repository.dart';
import '../models/splash_image_data.dart';

final splashImageProvider = FutureProvider<SplashImageData>((ref) {
  return ref.read(splashRepositoryProvider).loadSplashImage();
});
