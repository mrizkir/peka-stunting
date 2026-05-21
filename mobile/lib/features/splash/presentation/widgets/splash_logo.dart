import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../../core/config/app_config.dart';
import '../../../../core/theme/app_theme.dart';

/// Logo splash: URL backend → asset lokal → teks.
class SplashLogo extends StatefulWidget {
  const SplashLogo({
    super.key,
    this.remoteUrl,
    this.size = 120,
  });

  final String? remoteUrl;
  final double size;

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
      return Image.network(
        remoteUrl,
        width: widget.size,
        height: widget.size,
        fit: BoxFit.contain,
        errorBuilder: (_, __, ___) => _buildLocalOrText(),
        loadingBuilder: (context, child, progress) {
          if (progress == null) {
            return child;
          }
          return SizedBox(
            width: widget.size,
            height: widget.size,
            child: const Center(
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: Colors.white,
              ),
            ),
          );
        },
      );
    }

    return _buildLocalOrText();
  }

  Widget _buildLocalOrText() {
    if (_hasLocalAsset == true) {
      return Image.asset(
        SplashLogo.assetPath,
        width: widget.size,
        height: widget.size,
        fit: BoxFit.contain,
      );
    }

    if (_hasLocalAsset == false) {
      return const _SplashTextLogo();
    }

    return SizedBox(
      width: widget.size,
      height: widget.size,
      child: const Center(
        child: CircularProgressIndicator(
          strokeWidth: 2,
          color: Colors.white,
        ),
      ),
    );
  }
}

class _SplashTextLogo extends StatelessWidget {
  const _SplashTextLogo();

  @override
  Widget build(BuildContext context) {
    return Container(
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
    );
  }
}
