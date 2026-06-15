import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../../core/config/app_config.dart';
import '../../../../core/theme/app_theme.dart';

/// Gambar splash full screen: URL backend → asset lokal → fallback teks.
class SplashLogo extends StatefulWidget {
  const SplashLogo({
    super.key,
    this.remoteUrl,
  });

  final String? remoteUrl;

  static const String assetPath = 'assets/images/splash_logo.png';

  @override
  State<SplashLogo> createState() => _SplashLogoState();
}

class _SplashLogoState extends State<SplashLogo> {
  bool? _hasLocalAsset;

  @override
  void initState() {
    super.initState();
    _detectLocalAsset();
  }

  Future<void> _detectLocalAsset() async {
    try {
      await rootBundle.load(SplashLogo.assetPath);
      if (mounted) {
        setState(() => _hasLocalAsset = true);
      }
    } catch (_) {
      if (mounted) {
        setState(() => _hasLocalAsset = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final remoteUrl = widget.remoteUrl?.trim();
    if (remoteUrl != null && remoteUrl.isNotEmpty) {
      return _FullScreenImage(
        child: Image.network(
          remoteUrl,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _buildLocalOrText(),
          loadingBuilder: (context, child, progress) {
            if (progress == null) {
              return child;
            }
            if (_hasLocalAsset == true) {
              return Image.asset(
                SplashLogo.assetPath,
                fit: BoxFit.cover,
              );
            }
            return _buildLocalOrText(showLoading: true);
          },
        ),
      );
    }

    return _buildLocalOrText();
  }

  Widget _buildLocalOrText({bool showLoading = false}) {
    if (_hasLocalAsset == true) {
      return _FullScreenImage(
        child: Image.asset(
          SplashLogo.assetPath,
          fit: BoxFit.cover,
        ),
      );
    }

    if (_hasLocalAsset == false) {
      return const _SplashFallback();
    }

    return _FullScreenImage(
      backgroundColor: AppTheme.primary,
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

class _SplashFallback extends StatelessWidget {
  const _SplashFallback();

  @override
  Widget build(BuildContext context) {
    return ColoredBox(
      color: AppTheme.primary,
      child: Center(
        child: Container(
          height: 96,
          width: 96,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.12),
                blurRadius: 24,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          alignment: Alignment.center,
          child: Text(
            AppConfig.appName.split(' ').first,
            style: const TextStyle(
              color: AppTheme.primary,
              fontSize: 32,
              fontWeight: FontWeight.bold,
              letterSpacing: -0.5,
            ),
          ),
        ),
      ),
    );
  }
}
