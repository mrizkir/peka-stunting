import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/storage/education_content_cache.dart';
import '../../../core/utils/profile_image_picker.dart';
import '../../../core/widgets/profile_avatar.dart';
import '../../auth/providers/auth_provider.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _cache = EducationContentCache();
  bool _isDeleting = false;
  bool _clearing = false;
  bool _uploadingPhoto = false;

  Future<void> _showPhotoOptions() async {
    final user = ref.read(authStateProvider).valueOrNull;
    if (user == null || _uploadingPhoto) {
      return;
    }

    final action = await showModalBottomSheet<String>(
      context: context,
      showDragHandle: true,
      builder: (context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                leading: const Icon(Icons.photo_library_outlined),
                title: const Text('Pilih dari galeri'),
                onTap: () => Navigator.of(context).pop('gallery'),
              ),
              ListTile(
                leading: const Icon(Icons.photo_camera_outlined),
                title: const Text('Ambil foto'),
                onTap: () => Navigator.of(context).pop('camera'),
              ),
              if (user.profilePhotoUrl != null)
                ListTile(
                  leading: Icon(
                    Icons.delete_outline,
                    color: Theme.of(context).colorScheme.error,
                  ),
                  title: Text(
                    'Hapus foto profil',
                    style: TextStyle(
                      color: Theme.of(context).colorScheme.error,
                    ),
                  ),
                  onTap: () => Navigator.of(context).pop('delete'),
                ),
            ],
          ),
        );
      },
    );

    if (!mounted || action == null) {
      return;
    }

    // Tunggu bottom sheet selesai ditutup sebelum membuka picker native.
    await Future<void>.delayed(const Duration(milliseconds: 300));
    if (!mounted) {
      return;
    }

    switch (action) {
      case 'gallery':
        await _pickAndUploadPhoto(ImageSource.gallery);
      case 'camera':
        await _pickAndUploadPhoto(ImageSource.camera);
      case 'delete':
        await _deletePhoto();
    }
  }

  Future<void> _pickAndUploadPhoto(ImageSource source) async {
    XFile? picked;
    try {
      picked = source == ImageSource.gallery
          ? await ProfileImagePicker.pickFromGallery()
          : await ProfileImagePicker.pickFromCamera();
    } on GalleryPermissionDeniedException {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text(
            'Izin galeri diperlukan untuk memilih foto profil.',
          ),
          behavior: SnackBarBehavior.floating,
          action: SnackBarAction(
            label: 'Pengaturan',
            onPressed: ProfileImagePicker.openSettings,
          ),
        ),
      );
      return;
    } on PlatformException catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ProfileImagePicker.messageForError(error, source)),
          behavior: SnackBarBehavior.floating,
          action: error.code == 'photo_access_denied' ||
                  error.code == 'no_valid_image_uri' ||
                  error.code == 'missing_valid_image_uri'
              ? SnackBarAction(
                  label: 'Pengaturan',
                  onPressed: ProfileImagePicker.openSettings,
                )
              : null,
        ),
      );
      return;
    }

    if (picked == null || !mounted) {
      return;
    }

    setState(() => _uploadingPhoto = true);
    try {
      await ref
          .read(authStateProvider.notifier)
          .uploadProfilePhoto(picked.path);
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Foto profil berhasil diperbarui.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } on ApiException catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error.displayMessage),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } catch (_) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Gagal mengunggah foto profil. Coba lagi.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _uploadingPhoto = false);
      }
    }
  }

  Future<void> _deletePhoto() async {
    setState(() => _uploadingPhoto = true);
    try {
      await ref.read(authStateProvider.notifier).deleteProfilePhoto();
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Foto profil dihapus.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } on ApiException catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error.displayMessage),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } catch (_) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Gagal menghapus foto profil. Coba lagi.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _uploadingPhoto = false);
      }
    }
  }

  Future<void> _clearCache() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Clear Cache'),
          content: const Text(
            'Cache konten dan taksonomi akan dihapus. '
            'Aplikasi akan mengambil ulang data dari server.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Batal'),
            ),
            FilledButton(
              onPressed: () => Navigator.of(context).pop(true),
              child: const Text('Hapus'),
            ),
          ],
        );
      },
    );

    if (confirmed != true) {
      return;
    }

    setState(() => _clearing = true);
    try {
      await _cache.clearAll();
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Cache berhasil dihapus.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } catch (_) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Gagal menghapus cache. Coba lagi.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _clearing = false);
      }
    }
  }

  Future<void> _confirmDeleteAccount() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Hapus akun?'),
          content: const Text(
            'Akun dan riwayat skrining Anda akan dihapus permanen. '
            'Tindakan ini tidak dapat dibatalkan.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Batal'),
            ),
            FilledButton(
              style: FilledButton.styleFrom(
                backgroundColor: Theme.of(context).colorScheme.error,
              ),
              onPressed: () => Navigator.of(context).pop(true),
              child: const Text('Hapus permanen'),
            ),
          ],
        );
      },
    );

    if (confirmed != true || !mounted) {
      return;
    }

    setState(() => _isDeleting = true);
    try {
      await ref.read(authStateProvider.notifier).deleteAccount();
      if (!mounted) {
        return;
      }
      context.go('/login');
    } on ApiException catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error.displayMessage),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } catch (_) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Gagal menghapus akun. Periksa koneksi lalu coba lagi.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isDeleting = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authStateProvider).valueOrNull;

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          tooltip: 'Kembali',
          onPressed: () => StatefulNavigationShell.of(context).goBranch(
            0,
            initialLocation: false,
          ),
        ),
        title: const Text('Profil'),
      ),
      body: user == null
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: () =>
                  ref.read(authStateProvider.notifier).refreshUser(),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                children: [
                  Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Stack(
                          alignment: Alignment.center,
                          children: [
                            ProfileAvatar(
                              name: user.name,
                              profilePhotoUrl: user.profilePhotoUrl,
                              radius: 48,
                              showEditBadge: !_uploadingPhoto,
                              onTap: _uploadingPhoto ? null : _showPhotoOptions,
                            ),
                            if (_uploadingPhoto)
                              const SizedBox(
                                width: 96,
                                height: 96,
                                child: CircularProgressIndicator(strokeWidth: 3),
                              ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Ketuk foto untuk mengganti',
                          style: TextStyle(
                            color: Colors.grey.shade600,
                            fontSize: 13,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Tarik ke bawah untuk memperbarui profil',
                          style: TextStyle(
                            color: Colors.grey.shade500,
                            fontSize: 12,
                          ),
                        ),
                        const SizedBox(height: 20),
                        Align(
                          alignment: Alignment.centerLeft,
                          child: Text(
                            user.name,
                            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        _InfoRow(label: 'Email', value: user.email),
                        if (user.phone != null && user.phone!.isNotEmpty)
                          _InfoRow(label: 'No. HP', value: user.phone!),
                        if (user.roles.isNotEmpty)
                          _InfoRow(
                            label: 'Role',
                            value: user.roles.join(', '),
                          ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Penyimpanan',
                          style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                fontWeight: FontWeight.w600,
                              ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Hapus cache konten dan taksonomi jika data tampak '
                          'tidak terbaru.',
                          style: TextStyle(
                            color: Colors.grey.shade700,
                            height: 1.4,
                            fontSize: 14,
                          ),
                        ),
                        const SizedBox(height: 12),
                        FilledButton.icon(
                          onPressed: _clearing ? null : _clearCache,
                          icon: _clearing
                              ? const SizedBox(
                                  width: 16,
                                  height: 16,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : const Icon(Icons.cleaning_services_outlined),
                          label: Text(_clearing ? 'Menghapus...' : 'Clear Cache'),
                        ),
                      ],
                    ),
                  ),
                ),
                if (user.canDeleteAccount) ...[
                  const SizedBox(height: 24),
                  Text(
                    'Zona berbahaya',
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.w600,
                          color: Theme.of(context).colorScheme.error,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Menghapus akun akan menghapus data skrining yang '
                    'terkait dengan akun ini.',
                    style: TextStyle(
                      color: Colors.grey.shade700,
                      height: 1.4,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 12),
                  OutlinedButton.icon(
                    onPressed: _isDeleting ? null : _confirmDeleteAccount,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Theme.of(context).colorScheme.error,
                      side: BorderSide(
                        color: Theme.of(context).colorScheme.error,
                      ),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    icon: _isDeleting
                        ? SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Theme.of(context).colorScheme.error,
                            ),
                          )
                        : const Icon(Icons.delete_forever_outlined),
                    label: const Text('Hapus akun'),
                  ),
                ],
              ],
            ),
            ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({
    required this.label,
    required this.value,
  });

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 72,
            child: Text(
              label,
              style: TextStyle(color: Colors.grey.shade600, fontSize: 14),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontWeight: FontWeight.w500,
                fontSize: 14,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
