import 'package:flutter/material.dart';

import '../../../../core/config/app_config.dart';
import '../../../../core/theme/app_theme.dart';

/// Gambar splash: URL backend → asset lokal [AppConfig.logoAssetPath].
class SplashLogo extends StatelessWidget {
  const SplashLogo({
    super.key,
    this.remoteUrl,
  });

  final String? remoteUrl;

  @override
  Widget build(BuildContext context) {
    final remoteUrl = this.remoteUrl?.trim();
    if (remoteUrl != null && remoteUrl.isNotEmpty) {
      return _FullScreenImage(
        backgroundColor: AppTheme.primary,
        child: Image.network(
          remoteUrl,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _localLogo(),
          loadingBuilder: (context, child, progress) {
            if (progress == null) {
              return child;
            }
            return _localLogo(showLoading: true);
          },
        ),
      );
    }

    return _localLogo();
  }

  Widget _localLogo({bool showLoading = false}) {
    return _FullScreenImage(
      backgroundColor: AppTheme.primary,
      child: Stack(
        alignment: Alignment.center,
        children: [
          Image.asset(
            AppConfig.logoAssetPath,
            width: 200,
            height: 200,
            fit: BoxFit.contain,
          ),
          if (showLoading)
            const CircularProgressIndicator(
              strokeWidth: 2.5,
              color: Colors.white,
            ),
        ],
      ),
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
