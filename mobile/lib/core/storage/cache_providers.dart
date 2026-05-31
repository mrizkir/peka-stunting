import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../network/api_client.dart';
import 'education_content_cache.dart';
import 'poster_image_cache.dart';

final educationContentCacheProvider = Provider<EducationContentCache>((ref) {
  return EducationContentCache();
});

final posterImageCacheProvider = Provider<PosterImageCache>((ref) {
  return PosterImageCache(
    ref.read(educationContentCacheProvider),
    ref.read(dioProvider),
  );
});
