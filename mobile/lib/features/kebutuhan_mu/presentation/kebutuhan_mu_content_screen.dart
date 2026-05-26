import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../deteksi_dini/presentation/cek_imt_screen.dart';
import '../../deteksi_dini/presentation/cek_lila_screen.dart';
import '../../deteksi_dini/presentation/cek_risiko_anemia_screen.dart';
import '../../mengenal_stunting/presentation/widgets/mengenal_stunting_body_html.dart';
import '../data/kebutuhan_mu_repository.dart';
import '../kebutuhan_mu_mock_data.dart';
import '../models/kebutuhan_mu_models.dart';

typedef KebutuhanMuContentArgs = ({String menuSlug, String itemSlug});

final kebutuhanMuContentDetailProvider = FutureProvider.family<
    KebutuhanMuContent,
    KebutuhanMuContentArgs>((ref, args) {
  return ref.read(kebutuhanMuRepositoryProvider).fetchContent(
        menuSlug: args.menuSlug,
        itemSlug: args.itemSlug,
      );
});

/// Dispatcher: kalkulator vs materi dari API (poster + teks).
class KebutuhanMuContentScreen extends ConsumerWidget {
  const KebutuhanMuContentScreen({
    super.key,
    required this.menuSlug,
    required this.itemSlug,
  });

  final String menuSlug;
  final String itemSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    switch (itemSlug) {
      case 'cek-imt':
        return CekImtScreen(menuSlug: menuSlug);
      case 'cek-lila':
        return CekLilaScreen(menuSlug: menuSlug);
      case 'cek-risiko-anemia':
        return CekRisikoAnemiaScreen(menuSlug: menuSlug);
      default:
        break;
    }

    final args = (menuSlug: menuSlug, itemSlug: itemSlug);
    final contentAsync = ref.watch(kebutuhanMuContentDetailProvider(args));
    final mockItem = KebutuhanMuMockData.findItem(itemSlug);

    Future<void> refresh() async {
      ref.invalidate(kebutuhanMuContentDetailProvider(args));
      await ref.read(kebutuhanMuContentDetailProvider(args).future);
    }

    return Scaffold(
      appBar: AppBar(
        title: contentAsync.maybeWhen(
          data: (content) => Text(content.title),
          orElse: () => Text(mockItem?.name ?? 'Materi'),
        ),
        actions: [
          IconButton(
            onPressed: () => refresh(),
            tooltip: 'Muat ulang',
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: refresh,
        child: contentAsync.when(
          loading: () => ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            children: const [
              SizedBox(height: 200),
              Center(child: CircularProgressIndicator()),
            ],
          ),
          error: (error, _) => ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(20),
            children: [
              Text(error.toString()),
              const SizedBox(height: 16),
              const Text(
                'Pastikan konten sudah dipublikasikan di CMS '
                '(Kelola edukasi) dan URL API benar. Tarik ke bawah '
                'atau ketuk refresh untuk coba lagi.',
              ),
            ],
          ),
          data: (content) {
            final media = MediaQuery.of(context);
            final posterHeight = media.size.height -
                media.padding.top -
                kToolbarHeight;

            return _ContentWithPosters(
              content: content,
              posterHeight: posterHeight,
            );
          },
        ),
      ),
    );
  }
}

class _ContentWithPosters extends StatefulWidget {
  const _ContentWithPosters({
    required this.content,
    required this.posterHeight,
  });

  final KebutuhanMuContent content;
  final double posterHeight;

  @override
  State<_ContentWithPosters> createState() => _ContentWithPostersState();
}

class _ContentWithPostersState extends State<_ContentWithPosters> {
  bool _posterPointerActive = false;
  bool _posterZoomed = false;

  void _onPosterPointer(bool active) {
    if (_posterPointerActive == active) {
      return;
    }
    setState(() => _posterPointerActive = active);
  }

  void _onPosterZoomed(bool zoomed) {
    if (_posterZoomed == zoomed) {
      return;
    }
    setState(() => _posterZoomed = zoomed);
  }

  @override
  Widget build(BuildContext context) {
    final content = widget.content;
    final posterUrls = content.posterImages;
    final hasPoster = posterUrls.isNotEmpty;
    final hasBodyText = content.body?.trim().isNotEmpty ?? false;
    final hasExcerpt = content.excerpt?.trim().isNotEmpty ?? false;
    final lockVerticalScroll = _posterPointerActive || _posterZoomed;

    return CustomScrollView(
      physics: lockVerticalScroll
          ? const NeverScrollableScrollPhysics()
          : const AlwaysScrollableScrollPhysics(),
      slivers: [
        if (hasPoster)
          SliverToBoxAdapter(
            child: Listener(
              behavior: HitTestBehavior.opaque,
              onPointerDown: (_) => _onPosterPointer(true),
              onPointerUp: (_) => _onPosterPointer(false),
              onPointerCancel: (_) => _onPosterPointer(false),
              child: SizedBox(
                height: widget.posterHeight,
                child: _PosterCarousel(
                  urls: posterUrls,
                  height: widget.posterHeight,
                  onZoomActiveChanged: _onPosterZoomed,
                ),
              ),
            ),
          ),
        SliverPadding(
          padding: const EdgeInsets.all(20),
          sliver: SliverList(
            delegate: SliverChildListDelegate([
              if (!hasPoster && !hasExcerpt && !hasBodyText)
                Padding(
                  padding: const EdgeInsets.only(top: 16),
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Text(
                        'Belum ada poster atau teks. Unggah galeri poster '
                        'di halaman CMS untuk materi ini.',
                        style: TextStyle(
                          color: Colors.grey.shade700,
                          height: 1.5,
                        ),
                      ),
                    ),
                  ),
                ),
              if (hasExcerpt) ...[
                const SizedBox(height: 12),
                Text(
                  content.excerpt!,
                  style: TextStyle(
                    color: Colors.grey.shade700,
                    fontSize: 16,
                  ),
                ),
              ],
              if (hasBodyText) ...[
                const SizedBox(height: 16),
                MengenalStuntingBodyHtml(html: content.body),
              ],
              const SizedBox(height: 24),
            ]),
          ),
        ),
      ],
    );
  }
}

class _EducationPosterImage extends StatefulWidget {
  const _EducationPosterImage({
    super.key,
    required this.url,
    this.height,
    required this.isCurrentPage,
    this.onZoomActiveChanged,
  });

  final String url;
  final double? height;
  final bool isCurrentPage;
  final ValueChanged<bool>? onZoomActiveChanged;

  @override
  State<_EducationPosterImage> createState() => _EducationPosterImageState();
}

class _EducationPosterImageState extends State<_EducationPosterImage> {
  static const _minScale = 1.0;
  static const _maxScale = 5.0;
  static const _zoomedThreshold = 1.05;

  final TransformationController _transformController =
      TransformationController();
  bool _reportsPageLocked = false;
  bool _isZoomed = false;

  @override
  void dispose() {
    _transformController.dispose();
    super.dispose();
  }

  @override
  void didUpdateWidget(covariant _EducationPosterImage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.isCurrentPage != widget.isCurrentPage) {
      _resetZoom();
    }
  }

  double get _currentScale => _transformController.value.getMaxScaleOnAxis();

  void _resetZoom() {
    _transformController.value = Matrix4.identity();
    _setPageLocked(false);
  }

  void _setPageLocked(bool locked) {
    if (_reportsPageLocked == locked && _isZoomed == locked) {
      return;
    }
    _reportsPageLocked = locked;
    widget.onZoomActiveChanged?.call(locked);
    setState(() => _isZoomed = locked);
  }

  void _syncPageLockFromScale() {
    _setPageLocked(_currentScale > _zoomedThreshold);
  }

  void _onInteractionEnd(ScaleEndDetails details) {
    if (_currentScale < _zoomedThreshold) {
      _resetZoom();
      return;
    }
    _syncPageLockFromScale();
  }

  @override
  Widget build(BuildContext context) {
    final image = Image.network(
      widget.url,
      fit: BoxFit.contain,
      gaplessPlayback: true,
      loadingBuilder: (context, child, loadingProgress) {
        if (loadingProgress == null) {
          return child;
        }
        return Center(
          child: CircularProgressIndicator(
            value: loadingProgress.expectedTotalBytes != null
                ? loadingProgress.cumulativeBytesLoaded /
                    loadingProgress.expectedTotalBytes!
                : null,
            color: Colors.white,
          ),
        );
      },
      errorBuilder: (context, error, stackTrace) {
        debugPrint('Failed to load poster image: ${widget.url}');
        debugPrint('Error: $error');
        if (stackTrace != null) {
          debugPrint('StackTrace: $stackTrace');
        }

        return Padding(
          padding: const EdgeInsets.all(24),
          child: Text(
            'Gagal memuat gambar.\n\nURL:\n${widget.url}\n\nError:\n$error',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.grey.shade300),
          ),
        );
      },
    );

    final poster = RepaintBoundary(
      child: InteractiveViewer(
        transformationController: _transformController,
        minScale: _minScale,
        maxScale: _maxScale,
        panEnabled: true,
        scaleEnabled: true,
        clipBehavior: Clip.hardEdge,
        onInteractionStart: (_) => _syncPageLockFromScale(),
        onInteractionUpdate: (_) => _syncPageLockFromScale(),
        onInteractionEnd: _onInteractionEnd,
        child: image,
      ),
    );

    final layered = Stack(
      fit: StackFit.expand,
      children: [
        poster,
        if (_isZoomed)
          Positioned(
            right: 16,
            top: 16,
            child: Material(
              color: Colors.black54,
              shape: const CircleBorder(),
              child: IconButton(
                onPressed: _resetZoom,
                tooltip: 'Ukuran normal',
                color: Colors.white,
                icon: const Icon(Icons.zoom_out_map),
              ),
            ),
          ),
      ],
    );

    if (widget.height == null) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(12),
        child: layered,
      );
    }

    return ColoredBox(
      color: Colors.black,
      child: layered,
    );
  }
}

class _PosterCarousel extends StatefulWidget {
  const _PosterCarousel({
    required this.urls,
    required this.height,
    required this.onZoomActiveChanged,
  });

  final List<String> urls;
  final double height;
  final ValueChanged<bool> onZoomActiveChanged;

  @override
  State<_PosterCarousel> createState() => _PosterCarouselState();
}

class _PosterCarouselState extends State<_PosterCarousel> {
  late final PageController _controller;
  final ValueNotifier<bool> _horizontalScrollLocked = ValueNotifier(false);
  int _currentPage = 0;

  @override
  void initState() {
    super.initState();
    _controller = PageController();
  }

  @override
  void dispose() {
    _horizontalScrollLocked.dispose();
    _controller.dispose();
    super.dispose();
  }

  void _onZoomActiveChanged(bool isActive) {
    if (_horizontalScrollLocked.value == isActive) {
      return;
    }
    _horizontalScrollLocked.value = isActive;
    widget.onZoomActiveChanged(isActive);
  }

  @override
  Widget build(BuildContext context) {
    final pageView = ValueListenableBuilder<bool>(
      valueListenable: _horizontalScrollLocked,
      builder: (context, locked, _) {
        return PageView.builder(
          controller: _controller,
          physics: locked
              ? const NeverScrollableScrollPhysics()
              : const PageScrollPhysics(),
          itemCount: widget.urls.length,
          onPageChanged: (index) {
            setState(() => _currentPage = index);
            _horizontalScrollLocked.value = false;
            widget.onZoomActiveChanged(false);
          },
          itemBuilder: (context, index) => _EducationPosterImage(
            key: ValueKey(widget.urls[index]),
            url: widget.urls[index],
            height: widget.height,
            isCurrentPage: index == _currentPage,
            onZoomActiveChanged: _onZoomActiveChanged,
          ),
        );
      },
    );

    if (widget.urls.length == 1) {
      return pageView;
    }

    return Stack(
      fit: StackFit.expand,
      children: [
        pageView,
        Positioned(
          left: 0,
          right: 0,
          bottom: 16,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(widget.urls.length, (index) {
              final isActive = index == _currentPage;
              return AnimatedContainer(
                duration: const Duration(milliseconds: 180),
                margin: const EdgeInsets.symmetric(horizontal: 3),
                width: isActive ? 14 : 8,
                height: 8,
                decoration: BoxDecoration(
                  color: isActive ? Colors.white : Colors.white54,
                  borderRadius: BorderRadius.circular(999),
                  boxShadow: const [
                    BoxShadow(
                      color: Colors.black26,
                      blurRadius: 4,
                    ),
                  ],
                ),
              );
            }),
          ),
        ),
      ],
    );
  }
}
