import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/date_symbol_data_local.dart';

import 'app.dart';
import 'core/utils/profile_image_picker.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  ProfileImagePicker.configureAndroidPhotoPicker(enabled: true);
  await initializeDateFormatting('id_ID');
  runApp(const ProviderScope(child: PekaStuntingApp()));
}
