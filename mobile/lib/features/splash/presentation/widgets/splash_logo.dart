import 'dart:io';

import 'package:flutter/material.dart';

/// Gambar splash dari cache disk atau URL backend (CMS).
class SplashLogo extends StatelessWidget {
  static const Color _backgroundColor = Color(0xFFA0C49D);
  const SplashLogo({
    super.key,
    this.remoteUrl,
    this.localPath,
    this.isLoading = false,
    this.onImageLoaded,
    this.onImageError,
  });

  final String? remoteUrl;
  final String? localPath;
  final bool isLoading;
  final VoidCallback? onImageLoaded;
  final VoidCallback? onImageError;

  @override
  Widget build(BuildContext context) {
    final localPath = this.localPath?.trim();
    if (localPath != null && localPath.isNotEmpty) {
      return _FullScreenImage(
        backgroundColor: _backgroundColor,
        child: Image.file(
          File(localPath),
          fit: BoxFit.cover,
          frameBuilder: (context, child, frame, wasSynchronouslyLoaded) {
            if (frame != null || wasSynchronouslyLoaded) {
              WidgetsBinding.instance.addPostFrameCallback((_) {
                onImageLoaded?.call();
              });
              return child;
            }
            return _placeholder(showLoading: true);
          },
          errorBuilder: (_, __, ___) {
            WidgetsBinding.instance.addPostFrameCallback((_) {
              onImageError?.call();
            });
            return _buildRemoteFallback();
          },
        ),
      );
    }

    return _buildRemoteFallback();
  }

  Widget _buildRemoteFallback() {
    final remoteUrl = this.remoteUrl?.trim();
    if (remoteUrl != null && remoteUrl.isNotEmpty) {
      return _FullScreenImage(
        backgroundColor: _backgroundColor,
        child: Image.network(
          remoteUrl,
          fit: BoxFit.cover,
          frameBuilder: (context, child, frame, wasSynchronouslyLoaded) {
            if (frame != null || wasSynchronouslyLoaded) {
              WidgetsBinding.instance.addPostFrameCallback((_) {
                onImageLoaded?.call();
              });
              return child;
            }
            return _placeholder(showLoading: true);
          },
          errorBuilder: (_, __, ___) {
            WidgetsBinding.instance.addPostFrameCallback((_) {
              onImageError?.call();
            });
            return _placeholder();
          },
        ),
      );
    }

    return _placeholder(showLoading: isLoading);
  }

  Widget _placeholder({bool showLoading = false}) {
    return _FullScreenImage(
      backgroundColor: _backgroundColor,
      child: showLoading
          ? const Center(
              child: CircularProgressIndicator(
                strokeWidth: 2.5,
                color: Colors.white,
              ),
            )
          : const SizedBox.shrink(),
    );
  }
}

class _FullScreenImage extends StatelessWidget {
  const _FullScreenImage({
    required this.child,
    this.backgroundColor,
  });

  final Widget child;
  final Color? backgroundColor;

  @override
  Widget build(BuildContext context) {
    return ColoredBox(
      color: backgroundColor ?? Colors.black,
      child: SizedBox.expand(
        child: child,
      ),
    );
  }
}
