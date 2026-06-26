import 'dart:async';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../storage/cache_providers.dart';

typedef CachedPosterImageErrorBuilder = Widget Function(
  BuildContext context,
  VoidCallback onRetry,
);

typedef CachedPosterImageLoadingBuilder = Widget Function(
  BuildContext context,
);

class CachedPosterImage extends ConsumerStatefulWidget {
  const CachedPosterImage({
    super.key,
    required this.url,
    required this.fit,
    this.gaplessPlayback = true,
    this.loadingBuilder,
    this.errorBuilder,
  });

  final String url;
  final BoxFit fit;
  final bool gaplessPlayback;
  final CachedPosterImageLoadingBuilder? loadingBuilder;
  final CachedPosterImageErrorBuilder? errorBuilder;

  @override
  ConsumerState<CachedPosterImage> createState() => _CachedPosterImageState();
}

class _CachedPosterImageState extends ConsumerState<CachedPosterImage> {
  String? _localPath;
  bool _isLoading = true;
  bool _hasError = false;

  @override
  void initState() {
    super.initState();
    unawaited(_loadPoster());
  }

  @override
  void didUpdateWidget(covariant CachedPosterImage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.url != widget.url) {
      _localPath = null;
      _isLoading = true;
      _hasError = false;
      unawaited(_loadPoster());
    }
  }

  Future<void> _loadPoster() async {
    setState(() {
      _isLoading = true;
      _hasError = false;
      _localPath = null;
    });

    final cache = ref.read(posterImageCacheProvider);
    var localPath = await cache.resolveLocalPath(widget.url);
    localPath ??= await cache.cacheUrl(widget.url);

    if (!mounted) {
      return;
    }

    if (localPath != null) {
      setState(() {
        _localPath = localPath;
        _isLoading = false;
        _hasError = false;
      });
      return;
    }

    setState(() {
      _isLoading = false;
      _hasError = true;
    });
  }

  Future<void> _handleFileError() async {
    await ref.read(posterImageCacheProvider).invalidateUrl(widget.url);
    await _loadPoster();
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return widget.loadingBuilder?.call(context) ??
          const Center(child: CircularProgressIndicator(color: Colors.white));
    }

    if (_hasError || _localPath == null) {
      return widget.errorBuilder?.call(context, _loadPoster) ??
          _defaultError(context);
    }

    return _buildImage(
      Image.file(
        File(_localPath!),
        fit: widget.fit,
        gaplessPlayback: widget.gaplessPlayback,
        errorBuilder: (context, error, stackTrace) {
          unawaited(_handleFileError());
          return widget.loadingBuilder?.call(context) ??
              const Center(
                child: CircularProgressIndicator(color: Colors.white),
              );
        },
      ),
    );
  }

  Widget _buildImage(Widget image) {
    switch (widget.fit) {
      case BoxFit.cover:
        return SizedBox.expand(child: image);
      case BoxFit.fitWidth:
        return SizedBox(width: double.infinity, child: image);
      default:
        return image;
    }
  }

  Widget _defaultError(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.image_not_supported_outlined, color: Colors.grey.shade400, size: 40),
            const SizedBox(height: 12),
            Text(
              'Gagal memuat gambar.',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Colors.grey.shade300,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Periksa koneksi internet lalu coba lagi.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey.shade500, fontSize: 13),
            ),
            const SizedBox(height: 16),
            TextButton.icon(
              onPressed: _loadPoster,
              icon: const Icon(Icons.refresh, color: Colors.white70),
              label: const Text('Coba lagi'),
              style: TextButton.styleFrom(foregroundColor: Colors.white70),
            ),
          ],
        ),
      ),
    );
  }
}
