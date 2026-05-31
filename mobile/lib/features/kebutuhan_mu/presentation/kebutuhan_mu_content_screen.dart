import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../deteksi_dini/presentation/cek_imt_screen.dart';
import '../../deteksi_dini/presentation/cek_lila_screen.dart';
import '../../deteksi_dini/presentation/cek_risiko_anemia_screen.dart';
import '../../deteksi_dini/presentation/periksa_status_gizi_screen.dart';
import '../../../core/widgets/cached_poster_image.dart';
import '../../../core/widgets/education_video_player.dart';
import '../data/kebutuhan_mu_repository.dart';
import '../kebutuhan_mu_mock_data.dart';
import '../models/kebutuhan_mu_models.dart';
import 'widgets/kebutuhan_mu_menu_description.dart';

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
      case 'periksa-status-gizi':
        return PeriksaStatusGiziScreen(menuSlug: menuSlug);
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
            return LayoutBuilder(
              builder: (context, constraints) {
                return _ContentWithPosters(
                  content: content,
                  posterHeight: constraints.maxHeight,
                );
              },
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
  bool _posterZoomed = false;

  void _onPosterZoomed(bool zoomed) {
    if (_posterZoomed == zoomed) {
      return;
    }
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) {
        return;
      }
      setState(() => _posterZoomed = zoomed);
    });
  }

  @override
  Widget build(BuildContext context) {
    final content = widget.content;
    final posterUrls = content.posterImages;
    final hasPoster = posterUrls.isNotEmpty;
    final hasVideo = content.videoUrl != null;
    final hasBodyText = content.body?.trim().isNotEmpty ?? false;
    final hasExcerpt = content.excerpt?.trim().isNotEmpty ?? false;
    final hasExtraContent = hasVideo || hasExcerpt || hasBodyText;
    final posterOnly = hasPoster && !hasExtraContent;
    final lockVerticalScroll = _posterZoomed;

    if (posterOnly) {
      return SingleChildScrollView(
        physics: lockVerticalScroll
            ? const NeverScrollableScrollPhysics()
            : const AlwaysScrollableScrollPhysics(),
        child: SizedBox(
          height: widget.posterHeight,
          width: double.infinity,
          child: _PosterTabViewer(
            key: ValueKey(posterUrls),
            urls: posterUrls,
            fillScreen: true,
            onZoomActiveChanged: _onPosterZoomed,
          ),
        ),
      );
    }

    return CustomScrollView(
      physics: lockVerticalScroll
          ? const NeverScrollableScrollPhysics()
          : const AlwaysScrollableScrollPhysics(),
      slivers: [
        if (hasPoster)
          SliverToBoxAdapter(
            child: SizedBox(
              height: widget.posterHeight,
              child: _PosterTabViewer(
                key: ValueKey(posterUrls),
                urls: posterUrls,
                fillScreen: false,
                onZoomActiveChanged: _onPosterZoomed,
              ),
            ),
          ),
        SliverPadding(
          padding: const EdgeInsets.all(20),
          sliver: SliverList(
            delegate: SliverChildListDelegate([
              if (!hasPoster && !hasVideo && !hasExcerpt && !hasBodyText)
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
              if (hasVideo) ...[
                EducationVideoPlayer(videoUrl: content.videoUrl!),
                const SizedBox(height: 16),
              ],
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
                KebutuhanMuMenuDescription(description: content.body!),
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
    required this.isCurrentPage,
    this.fillScreen = false,
    this.onZoomActiveChanged,
  });

  final String url;
  final bool isCurrentPage;
  final bool fillScreen;
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
  bool _isZoomed = false;
  int _pointerCount = 0;

  @override
  void dispose() {
    _transformController.dispose();
    super.dispose();
  }

  @override
  void didUpdateWidget(covariant _EducationPosterImage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.isCurrentPage &&
        !widget.isCurrentPage &&
        (_isZoomed || _currentScale > _zoomedThreshold)) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) {
          return;
        }
        _resetZoom();
      });
    }
  }

  double get _currentScale => _transformController.value.getMaxScaleOnAxis();

  void _resetZoom() {
    _transformController.value = Matrix4.identity();
    _pointerCount = 0;
    _setZoomed(false);
  }

  void _setZoomed(bool zoomed) {
    if (_isZoomed == zoomed) {
      return;
    }
    if (mounted) {
      setState(() => _isZoomed = zoomed);
    }
    widget.onZoomActiveChanged?.call(zoomed);
  }

  void _enterZoomMode({double initialScale = 1.0}) {
    if (_isZoomed) {
      return;
    }
    if (initialScale > 1.0) {
      _transformController.value =
          Matrix4.diagonal3Values(initialScale, initialScale, 1.0);
    }
    _setZoomed(true);
  }

  void _onPointerDown(PointerDownEvent event) {
    _pointerCount++;
    if (_pointerCount >= 2 && !_isZoomed) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted || _isZoomed) {
          return;
        }
        _enterZoomMode();
      });
    }
  }

  void _onPointerUp(PointerEvent event) {
    _pointerCount = (_pointerCount - 1).clamp(0, 10);
  }

  void _syncZoomFromScale() {
    if (_currentScale > _zoomedThreshold) {
      _setZoomed(true);
    }
  }

  void _onInteractionEnd(ScaleEndDetails details) {
    if (_currentScale < _zoomedThreshold) {
      _resetZoom();
      return;
    }
    _setZoomed(true);
  }

  @override
  Widget build(BuildContext context) {
    final image = CachedPosterImage(
      url: widget.url,
      fit: widget.fillScreen ? BoxFit.cover : BoxFit.contain,
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
      child: Listener(
        onPointerDown: _onPointerDown,
        onPointerUp: _onPointerUp,
        onPointerCancel: _onPointerUp,
        child: GestureDetector(
          onDoubleTap: () => _enterZoomMode(initialScale: 2.0),
          behavior: HitTestBehavior.translucent,
          child: InteractiveViewer(
            transformationController: _transformController,
            minScale: _minScale,
            maxScale: _maxScale,
            panEnabled: _isZoomed,
            scaleEnabled: _isZoomed || _pointerCount >= 2,
            clipBehavior: Clip.hardEdge,
            onInteractionStart: (_) => _syncZoomFromScale(),
            onInteractionUpdate: (_) => _syncZoomFromScale(),
            onInteractionEnd: _onInteractionEnd,
            child: image,
          ),
        ),
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

    return ColoredBox(
      color: Colors.black,
      child: layered,
    );
  }
}

class _PosterTabViewer extends StatefulWidget {
  const _PosterTabViewer({
    super.key,
    required this.urls,
    required this.fillScreen,
    required this.onZoomActiveChanged,
  });

  final List<String> urls;
  final bool fillScreen;
  final ValueChanged<bool> onZoomActiveChanged;

  @override
  State<_PosterTabViewer> createState() => _PosterTabViewerState();
}

class _PosterTabViewerState extends State<_PosterTabViewer>
    with SingleTickerProviderStateMixin {
  static const _tabBarColor = Color(0xFF374151);
  static const _tabIndicatorColor = Color(0xFF22D3EE);

  TabController? _tabController;
  PageController? _pageController;
  bool _scrollLocked = false;
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _syncTabController();
  }

  @override
  void didUpdateWidget(covariant _PosterTabViewer oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.urls.length != widget.urls.length) {
      _syncTabController();
    }
  }

  @override
  void dispose() {
    _disposeTabController();
    _disposePageController();
    super.dispose();
  }

  void _disposePageController() {
    _pageController?.dispose();
    _pageController = null;
  }

  void _disposeTabController() {
    _tabController?.removeListener(_onTabChanged);
    _tabController?.dispose();
    _tabController = null;
  }

  void _syncTabController() {
    if (widget.urls.length <= 1) {
      _disposeTabController();
      _disposePageController();
      _currentIndex = 0;
      _scrollLocked = false;
      return;
    }

    if (_tabController == null ||
        _tabController!.length != widget.urls.length) {
      _disposeTabController();
      _disposePageController();
      _currentIndex = 0;
      _scrollLocked = false;
      _tabController = TabController(length: widget.urls.length, vsync: this)
        ..addListener(_onTabChanged);
      _pageController = PageController();
    }
  }

  void _onTabChanged() {
    if (!mounted || _tabController == null) {
      return;
    }
    if (_tabController!.indexIsChanging || _scrollLocked) {
      return;
    }
    final index = _tabController!.index;
    if (_pageController?.hasClients == true && mounted) {
      final page = _pageController!.page ?? _currentIndex.toDouble();
      if (page.round() != index) {
        _pageController!.animateToPage(
          index,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeInOut,
        );
      }
    }
    if (_currentIndex != index) {
      setState(() => _currentIndex = index);
    }
  }

  void _onPageChanged(int index) {
    if (!mounted || _currentIndex == index) {
      return;
    }
    setState(() => _currentIndex = index);
    if (_tabController != null && _tabController!.index != index) {
      _tabController!.animateTo(index);
    }
  }

  void _onZoomActiveChanged(bool isActive) {
    if (_scrollLocked == isActive) {
      return;
    }

    void apply() {
      if (!mounted) {
        return;
      }
      setState(() => _scrollLocked = isActive);
      widget.onZoomActiveChanged(isActive);
    }

    if (isActive) {
      final controller = _pageController;
      if (controller != null && controller.hasClients) {
        controller.jumpToPage(_currentIndex);
      }
    }

    WidgetsBinding.instance.addPostFrameCallback((_) => apply());
  }

  @override
  Widget build(BuildContext context) {
    if (widget.urls.length == 1) {
      return _EducationPosterImage(
        key: ValueKey(widget.urls.first),
        url: widget.urls.first,
        fillScreen: widget.fillScreen,
        isCurrentPage: true,
        onZoomActiveChanged: widget.onZoomActiveChanged,
      );
    }

    return Column(
      children: [
        IgnorePointer(
          ignoring: _scrollLocked,
          child: ColoredBox(
            color: _tabBarColor,
            child: TabBar(
              controller: _tabController,
              isScrollable: widget.urls.length > 5,
              tabs: List.generate(
                widget.urls.length,
                (index) => Tab(text: 'Hal ${index + 1}'),
              ),
              labelColor: Colors.white,
              unselectedLabelColor: Colors.white70,
              indicatorColor: _tabIndicatorColor,
              indicatorWeight: 3,
              dividerColor: Colors.transparent,
              labelStyle: const TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 14,
              ),
              unselectedLabelStyle: const TextStyle(
                fontWeight: FontWeight.w500,
                fontSize: 14,
              ),
            ),
          ),
        ),
        Expanded(
          child: PageView.builder(
            controller: _pageController,
            physics: _scrollLocked
                ? const NeverScrollableScrollPhysics()
                : const PageScrollPhysics(),
            itemCount: widget.urls.length,
            onPageChanged: _onPageChanged,
            itemBuilder: (context, index) => _EducationPosterImage(
              key: ValueKey(widget.urls[index]),
              url: widget.urls[index],
              fillScreen: widget.fillScreen,
              isCurrentPage: index == _currentIndex,
              onZoomActiveChanged: _onZoomActiveChanged,
            ),
          ),
        ),
      ],
    );
  }
}
