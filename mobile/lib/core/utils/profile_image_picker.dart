import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:image_picker_android/image_picker_android.dart';
import 'package:image_picker_platform_interface/image_picker_platform_interface.dart';
import 'package:permission_handler/permission_handler.dart';

class ProfileImagePicker {
  ProfileImagePicker._();

  static final ImagePicker _picker = ImagePicker();

  static void configureAndroidPhotoPicker({required bool enabled}) {
    if (kIsWeb) {
      return;
    }
    final platform = ImagePickerPlatform.instance;
    if (platform is ImagePickerAndroid) {
      platform.useAndroidPhotoPicker = enabled;
    }
  }

  static Future<bool> ensureGalleryPermission() async {
    if (!Platform.isAndroid) {
      return true;
    }

    try {
      if (await Permission.photos.isGranted ||
          await Permission.storage.isGranted) {
        return true;
      }

      final photosStatus = await Permission.photos.request();
      if (photosStatus.isGranted || photosStatus.isLimited) {
        return true;
      }

      final storageStatus = await Permission.storage.request();
      return storageStatus.isGranted;
    } on MissingPluginException {
      // Plugin native belum ter-link (mis. setelah hot restart). Lewati cek
      // izin dan biarkan image_picker menangani pemilihan foto.
      return true;
    }
  }

  static Future<XFile?> pickFromGallery() async {
    if (!await ensureGalleryPermission()) {
      throw const GalleryPermissionDeniedException();
    }

    PlatformException? lastError;
    for (final usePhotoPicker in [true, false]) {
      configureAndroidPhotoPicker(enabled: usePhotoPicker);
      try {
        return await _picker.pickImage(
          source: ImageSource.gallery,
          maxWidth: 1024,
          maxHeight: 1024,
          imageQuality: 85,
          requestFullMetadata: false,
        );
      } on PlatformException catch (error) {
        lastError = error;
        if (!usePhotoPicker) {
          rethrow;
        }
      }
    }

    throw lastError ?? PlatformException(code: 'unknown', message: 'Galeri gagal');
  }

  static Future<XFile?> pickFromCamera() async {
    return _picker.pickImage(
      source: ImageSource.camera,
      maxWidth: 1024,
      maxHeight: 1024,
      imageQuality: 85,
      requestFullMetadata: false,
    );
  }

  static Future<void> openSettings() async {
    try {
      await openAppSettings();
    } on MissingPluginException {
      // Plugin native belum ter-link.
    }
  }

  static String messageForError(PlatformException error, ImageSource source) {
    return switch (error.code) {
      'camera_access_denied' =>
        'Izin kamera ditolak. Aktifkan di pengaturan perangkat.',
      'photo_access_denied' =>
        'Izin galeri ditolak. Aktifkan di pengaturan perangkat.',
      'no_available_camera' => 'Kamera tidak tersedia di perangkat ini.',
      'no_activity' =>
        'Aplikasi belum siap membuka galeri. Tunggu sebentar lalu coba lagi.',
      'already_active' =>
        'Pemilih foto sedang digunakan. Tunggu sebentar lalu coba lagi.',
      'no_valid_image_uri' || 'missing_valid_image_uri' =>
        'Tidak dapat membaca foto yang dipilih. Berikan izin galeri di pengaturan.',
      _ => source == ImageSource.gallery
          ? 'Gagal membuka galeri. Coba lagi.'
          : 'Gagal membuka kamera. Coba lagi.',
    };
  }
}

class GalleryPermissionDeniedException implements Exception {
  const GalleryPermissionDeniedException();
}
