import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/date_symbol_data_local.dart';

import 'app.dart';
import 'core/analytics/app_lifecycle_observer.dart';
import 'core/utils/profile_image_picker.dart';
import 'firebase_options.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );
  ProfileImagePicker.configureAndroidPhotoPicker(enabled: true);
  await initializeDateFormatting('id_ID');
  runApp(
    const ProviderScope(
      child: AppLifecycleObserver(
        child: PekaStuntingApp(),
      ),
    ),
  );
}
