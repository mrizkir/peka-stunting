import 'dart:async';

import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:video_player/video_player.dart';
import 'package:youtube_player_iframe/youtube_player_iframe.dart';

import '../config/app_config.dart';
import '../utils/education_video_url.dart';
import '../utils/youtube_url.dart';

final _youtubePlayerParams = YoutubePlayerParams(
  origin: AppConfig.youtubeEmbedOrigin,
  privacyEnhancedMode: true,
  showControls: true,
  showFullscreenButton: true,
  strictRelatedVideos: true,
  enableCaption: true,
  interfaceLanguage: 'id',
);

/// Pemutar video edukasi inline: YouTube embed atau file langsung (MP4/VPS).
class EducationVideoPlayer extends StatefulWidget {
  const EducationVideoPlayer({
    super.key,
    required this.videoUrl,
  });

  final String videoUrl;

  @override
  State<EducationVideoPlayer> createState() => _EducationVideoPlayerState();
}

class _EducationVideoPlayerState extends State<EducationVideoPlayer> {
  YoutubePlayerController? _youtubeController;
  StreamSubscription<YoutubePlayerValue>? _youtubeSub;
  VideoPlayerController? _fileController;
  Future<void>? _fileInitFuture;
  String? _errorMessage;
  bool? _wasVisible;
  Timer? _youtubeErrorTimer;

  @override
  void initState() {
    super.initState();
    _setupPlayer();
  }

  void _setupPlayer() {
    final url = widget.videoUrl.trim();
    final kind = EducationVideoUrl.kind(url);

    switch (kind) {
      case EducationVideoKind.youtube:
        final videoId = YoutubeUrl.videoId(url);
        if (videoId == null) {
          _errorMessage = 'Link YouTube tidak valid.';
          return;
        }
        _youtubeController = YoutubePlayerController.fromVideoId(
          videoId: videoId,
          params: _youtubePlayerParams,
        );
        _youtubeSub = _youtubeController!.stream.listen(_onYoutubeValue);
      case EducationVideoKind.directFile:
        final uri = Uri.tryParse(url);
        if (uri == null) {
          _errorMessage = 'URL video tidak valid.';
          return;
        }
        _fileController = VideoPlayerController.networkUrl(uri);
        _fileInitFuture = _fileController!.initialize().then((_) {
          if (mounted) {
            setState(() {});
          }
        }).catchError((Object error) {
          if (mounted) {
            setState(() {
              _errorMessage = 'Gagal memuat video: $error';
            });
          }
        });
      case EducationVideoKind.external:
        _errorMessage = null;
    }
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _pauseIfNotVisible();
  }

  void _pauseIfNotVisible() {
    final isVisible = TickerMode.of(context) &&
        (ModalRoute.of(context)?.isCurrent ?? true);

    if (_wasVisible == true && !isVisible) {
      _pausePlayback();
    }
    _wasVisible = isVisible;
  }

  void _onYoutubeValue(YoutubePlayerValue value) {
    if (!mounted) {
      return;
    }

    if (value.playerState == PlayerState.playing ||
        value.playerState == PlayerState.buffering) {
      _youtubeErrorTimer?.cancel();
      if (_errorMessage != null) {
        setState(() => _errorMessage = null);
      }
      return;
    }

    final error = value.error;
    if (error == YoutubeError.none ||
        error == YoutubeError.unknown ||
        error == YoutubeError.sameAsNotEmbeddable2) {
      // Error 152 sering muncul sementara saat init; abaikan.
      return;
    }

    _youtubeErrorTimer?.cancel();
    _youtubeErrorTimer = Timer(const Duration(seconds: 2), () {
      if (!mounted) {
        return;
      }

      final current = _youtubeController?.value;
      if (current == null) {
        return;
      }

      if (current.playerState == PlayerState.playing ||
          current.playerState == PlayerState.buffering) {
        return;
      }

      final currentError = current.error;
      if (currentError == YoutubeError.none ||
          currentError == YoutubeError.unknown ||
          currentError == YoutubeError.sameAsNotEmbeddable2) {
        return;
      }

      final message = _youtubeErrorMessage(currentError);
      if (_errorMessage != message) {
        setState(() => _errorMessage = message);
      }
    });
  }

  String _youtubeErrorMessage(YoutubeError error) {
    return switch (error) {
      YoutubeError.notEmbeddable ||
      YoutubeError.sameAsNotEmbeddable ||
      YoutubeError.sameAsNotEmbeddable2 =>
        'Video tidak dapat diputar di aplikasi. Buka di YouTube.',
      YoutubeError.videoNotFound || YoutubeError.cannotFindVideo =>
        'Video tidak ditemukan.',
      _ => 'Video tidak dapat diputar di aplikasi.',
    };
  }

  void _pausePlayback() {
    _youtubeController?.pauseVideo();
    _fileController?.pause();
  }

  @override
  void dispose() {
    _youtubeErrorTimer?.cancel();
    _youtubeSub?.cancel();
    _youtubeController?.close();
    _fileController?.dispose();
    super.dispose();
  }

  Uri? _externalVideoUri() {
    final trimmed = widget.videoUrl.trim();
    final videoId = YoutubeUrl.videoId(trimmed);
    if (videoId != null) {
      return Uri.parse('https://www.youtube.com/watch?v=$videoId');
    }
    return Uri.tryParse(trimmed);
  }

  Future<void> _openExternally() async {
    final uri = _externalVideoUri();
    if (uri == null) {
      return;
    }

    final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!launched && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Tidak dapat membuka link video.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Icon(Icons.ondemand_video, color: Colors.grey.shade700),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'Video edukasi',
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                  ),
                ),
                if (EducationVideoUrl.kind(widget.videoUrl) ==
                        EducationVideoKind.external ||
                    _youtubeController != null)
                  TextButton.icon(
                    onPressed: _openExternally,
                    icon: const Icon(Icons.open_in_new, size: 18),
                    label: const Text('Buka'),
                  ),
              ],
            ),
          ),
          AspectRatio(
            aspectRatio: 16 / 9,
            child: ColoredBox(
              color: Colors.black,
              child: _buildPlayerBody(context),
            ),
          ),
          if (_youtubeController != null && _errorMessage == null)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
              child: Text(
                'Ketuk beberapa kali untuk memutar video, atau klik tombol buka untuk membuka di YouTube.',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: Colors.grey.shade600,
                      height: 1.4,
                      fontStyle: FontStyle.italic,
                    ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildPlayerBody(BuildContext context) {
    if (_errorMessage != null) {
      return _ErrorPanel(
        message: _errorMessage!,
        onOpenExternal: _openExternally,
      );
    }

    final youtubeController = _youtubeController;
    if (youtubeController != null) {
      return YoutubePlayerThumbnail(
        controller: youtubeController,
        aspectRatio: 16 / 9,
        backgroundColor: Colors.black,
        playIcon: Material(
          color: Colors.black38,
          shape: const CircleBorder(),
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Icon(
              Icons.play_arrow,
              size: 48,
              color: Colors.white,
            ),
          ),
        ),
      );
    }

    final fileController = _fileController;
    if (fileController != null) {
      return FutureBuilder<void>(
        future: _fileInitFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const Center(
              child: CircularProgressIndicator(color: Colors.white),
            );
          }

          if (!fileController.value.isInitialized) {
            return _ErrorPanel(
              message: 'Video tidak dapat diputar di aplikasi.',
              onOpenExternal: _openExternally,
            );
          }

          return _InlineFilePlayer(controller: fileController);
        },
      );
    }

    return _ErrorPanel(
      message: 'Format video belum didukung untuk pemutaran inline.',
      onOpenExternal: _openExternally,
    );
  }
}

class _InlineFilePlayer extends StatefulWidget {
  const _InlineFilePlayer({required this.controller});

  final VideoPlayerController controller;

  @override
  State<_InlineFilePlayer> createState() => _InlineFilePlayerState();
}

class _InlineFilePlayerState extends State<_InlineFilePlayer> {
  @override
  void initState() {
    super.initState();
    widget.controller.addListener(_onTick);
  }

  @override
  void dispose() {
    widget.controller.removeListener(_onTick);
    super.dispose();
  }

  void _onTick() {
    if (mounted) {
      setState(() {});
    }
  }

  void _togglePlayback() {
    final controller = widget.controller;
    if (controller.value.isPlaying) {
      controller.pause();
    } else {
      controller.play();
    }
  }

  @override
  Widget build(BuildContext context) {
    final controller = widget.controller;
    final value = controller.value;

    return Stack(
      alignment: Alignment.center,
      children: [
        Center(
          child: AspectRatio(
            aspectRatio: value.aspectRatio == 0 ? 16 / 9 : value.aspectRatio,
            child: VideoPlayer(controller),
          ),
        ),
        if (!value.isPlaying)
          Material(
            color: Colors.black38,
            shape: const CircleBorder(),
            child: IconButton(
              onPressed: _togglePlayback,
              iconSize: 48,
              color: Colors.white,
              icon: const Icon(Icons.play_arrow),
            ),
          ),
        Positioned(
          left: 0,
          right: 0,
          bottom: 0,
          child: DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.bottomCenter,
                end: Alignment.topCenter,
                colors: [
                  Colors.black.withValues(alpha: 0.75),
                  Colors.transparent,
                ],
              ),
            ),
            child: Padding(
              padding: const EdgeInsets.fromLTRB(8, 16, 8, 8),
              child: Row(
                children: [
                  IconButton(
                    onPressed: _togglePlayback,
                    color: Colors.white,
                    icon: Icon(
                      value.isPlaying ? Icons.pause : Icons.play_arrow,
                    ),
                  ),
                  Expanded(
                    child: VideoProgressIndicator(
                      controller,
                      allowScrubbing: true,
                      colors: const VideoProgressColors(
                        playedColor: Colors.white,
                        bufferedColor: Colors.white38,
                        backgroundColor: Colors.white24,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _ErrorPanel extends StatelessWidget {
  const _ErrorPanel({
    required this.message,
    required this.onOpenExternal,
  });

  final String message;
  final VoidCallback onOpenExternal;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.error_outline, color: Colors.grey.shade400, size: 40),
          const SizedBox(height: 12),
          Text(
            message,
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.grey.shade300, height: 1.4),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: onOpenExternal,
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.white,
              side: const BorderSide(color: Colors.white54),
            ),
            icon: const Icon(Icons.open_in_new, size: 18),
            label: const Text('Buka di browser'),
          ),
        ],
      ),
    );
  }
}
