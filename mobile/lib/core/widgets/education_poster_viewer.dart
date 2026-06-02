import 'package:flutter/material.dart';

import 'cached_poster_image.dart';

/// Material breakpoint: shortest side >= 600dp → tablet / wide layout.
const _kTabletBreakpoint = 600.0;

/// Typical portrait poster aspect ratio (width : height).
const _kPosterAspectWidth = 3.0;
const _kPosterAspectHeight = 4.0;

bool _isWidePosterLayout(BuildContext context) {
  return MediaQuery.sizeOf(context).shortestSide >= _kTabletBreakpoint;
}

double _resolvePosterHeight(
  BuildContext context, {
  required double availableHeight,
  required bool posterOnly,
}) {
  if (!_isWidePosterLayout(context)) {
    return availableHeight;
  }

  if (posterOnly) {
    return availableHeight;
  }

  final width = MediaQuery.sizeOf(context).width;
  final heightFromAspect = width * _kPosterAspectHeight / _kPosterAspectWidth;

  return heightFromAspect.clamp(280.0, availableHeight);
}

BoxFit _resolvePosterFit(
  BuildContext context, {
  required bool posterOnly,
}) {
  if (_isWidePosterLayout(context)) {
    return BoxFit.fitWidth;
  }
  return posterOnly ? BoxFit.cover : BoxFit.contain;
}

/// Zoomable poster image with pinch/double-tap zoom support.
class EducationPosterImage extends StatefulWidget {
  const EducationPosterImage({
    super.key,
    required this.url,
    required this.isCurrentPage,
    this.imageFit = BoxFit.contain,
    this.onZoomActiveChanged,
  });

  final String url;
  final bool isCurrentPage;
  final BoxFit imageFit;
  final ValueChanged<bool>? onZoomActiveChanged;

  @override
  State<EducationPosterImage> createState() => _EducationPosterImageState();
}

class _EducationPosterImageState extends State<EducationPosterImage> {
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
  void didUpdateWidget(covariant EducationPosterImage oldWidget) {
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
      fit: widget.imageFit,
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
            child: SizedBox.expand(
              child: switch (widget.imageFit) {
                BoxFit.cover => image,
                BoxFit.fitWidth => Align(
                    alignment: Alignment.topCenter,
                    widthFactor: 1,
                    child: image,
                  ),
                _ => Center(child: image),
              },
            ),
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

/// Multi-page poster viewer with Hal 1 / Hal 2 tabs and swipe navigation.
class EducationPosterTabViewer extends StatefulWidget {
  const EducationPosterTabViewer({
    super.key,
    required this.urls,
    required this.imageFit,
    required this.onZoomActiveChanged,
  });

  final List<String> urls;
  final BoxFit imageFit;
  final ValueChanged<bool> onZoomActiveChanged;

  @override
  State<EducationPosterTabViewer> createState() =>
      _EducationPosterTabViewerState();
}

class _EducationPosterTabViewerState extends State<EducationPosterTabViewer>
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
  void didUpdateWidget(covariant EducationPosterTabViewer oldWidget) {
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
      return EducationPosterImage(
        key: ValueKey(widget.urls.first),
        url: widget.urls.first,
        imageFit: widget.imageFit,
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
            itemBuilder: (context, index) => EducationPosterImage(
              key: ValueKey(widget.urls[index]),
              url: widget.urls[index],
              imageFit: widget.imageFit,
              isCurrentPage: index == _currentIndex,
              onZoomActiveChanged: _onZoomActiveChanged,
            ),
          ),
        ),
      ],
    );
  }
}

/// Scroll layout: full-height poster gallery on top, optional content below.
class EducationPosterContentLayout extends StatefulWidget {
  const EducationPosterContentLayout({
    super.key,
    required this.posterUrls,
    required this.posterHeight,
    required this.contentChildren,
    this.emptyState,
  });

  final List<String> posterUrls;
  final double posterHeight;
  final List<Widget> contentChildren;
  final Widget? emptyState;

  @override
  State<EducationPosterContentLayout> createState() =>
      _EducationPosterContentLayoutState();
}

class _EducationPosterContentLayoutState
    extends State<EducationPosterContentLayout> {
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
    final posterUrls = widget.posterUrls;
    final hasPoster = posterUrls.isNotEmpty;
    final hasExtraContent = widget.contentChildren.isNotEmpty;
    final posterOnly = hasPoster && !hasExtraContent;
    final lockVerticalScroll = _posterZoomed;
    final effectiveHeight = _resolvePosterHeight(
      context,
      availableHeight: widget.posterHeight,
      posterOnly: posterOnly,
    );
    final imageFit = _resolvePosterFit(context, posterOnly: posterOnly);

    if (posterOnly) {
      return ColoredBox(
        color: Colors.black,
        child: SingleChildScrollView(
          physics: lockVerticalScroll
              ? const NeverScrollableScrollPhysics()
              : const AlwaysScrollableScrollPhysics(),
          child: SizedBox(
            height: effectiveHeight,
            width: double.infinity,
            child: EducationPosterTabViewer(
              key: ValueKey(posterUrls),
              urls: posterUrls,
              imageFit: imageFit,
              onZoomActiveChanged: _onPosterZoomed,
            ),
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
            child: ColoredBox(
              color: Colors.black,
              child: SizedBox(
                height: effectiveHeight,
                width: double.infinity,
                child: EducationPosterTabViewer(
                  key: ValueKey(posterUrls),
                  urls: posterUrls,
                  imageFit: imageFit,
                  onZoomActiveChanged: _onPosterZoomed,
                ),
              ),
            ),
          ),
        SliverPadding(
          padding: const EdgeInsets.all(20),
          sliver: SliverList(
            delegate: SliverChildListDelegate([
              if (!hasPoster && !hasExtraContent && widget.emptyState != null)
                widget.emptyState!,
              ...widget.contentChildren,
              const SizedBox(height: 24),
            ]),
          ),
        ),
      ],
    );
  }
}
