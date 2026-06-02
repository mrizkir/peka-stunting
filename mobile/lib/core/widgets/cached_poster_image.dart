import 'dart:async';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../storage/cache_providers.dart';

typedef CachedPosterImageErrorBuilder = Widget Function(
  BuildContext context,
  Object error,
  StackTrace? stackTrace,
);

typedef CachedPosterImageLoadingBuilder = Widget Function(
  BuildContext context,
  Widget child,
  ImageChunkEvent? loadingProgress,
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
  bool _resolved = false;

  @override
  void initState() {
    super.initState();
    _resolveLocalPath();
  }

  @override
  void didUpdateWidget(covariant CachedPosterImage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.url != widget.url) {
      _localPath = null;
      _resolved = false;
      _resolveLocalPath();
    }
  }

  Future<void> _resolveLocalPath() async {
    final cache = ref.read(posterImageCacheProvider);
    final localPath = await cache.resolveLocalPath(widget.url);
    if (!mounted) {
      return;
    }
    setState(() {
      _localPath = localPath;
      _resolved = true;
    });
    if (localPath == null) {
      unawaited(
        cache.cacheUrl(widget.url).then((_) {
          if (mounted) {
            _resolveLocalPath();
          }
        }),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    if (!_resolved) {
      return widget.loadingBuilder?.call(context, const SizedBox.shrink(), null) ??
          const Center(child: CircularProgressIndicator(color: Colors.white));
    }

    if (_localPath != null) {
      return _buildImage(Image.file(
        File(_localPath!),
        fit: widget.fit,
        gaplessPlayback: widget.gaplessPlayback,
        errorBuilder: (context, error, stackTrace) {
          return widget.errorBuilder?.call(context, error, stackTrace) ??
              _defaultError(context, error);
        },
      ));
    }

    return _buildImage(Image.network(
      widget.url,
      fit: widget.fit,
      gaplessPlayback: widget.gaplessPlayback,
      loadingBuilder: widget.loadingBuilder,
      errorBuilder: (context, error, stackTrace) {
        return widget.errorBuilder?.call(context, error, stackTrace) ??
            _defaultError(context, error);
      },
    ));
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

  Widget _defaultError(BuildContext context, Object error) {
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Text(
        'Gagal memuat gambar.\n\nURL:\n${widget.url}\n\nError:\n$error',
        textAlign: TextAlign.center,
        style: TextStyle(color: Colors.grey.shade300),
      ),
    );
  }
}
