class SplashImageData {
  const SplashImageData({
    this.remoteUrl,
    this.localPath,
  });

  final String? remoteUrl;
  final String? localPath;

  bool get hasDisplayableImage {
    final localPath = this.localPath?.trim();
    if (localPath != null && localPath.isNotEmpty) {
      return true;
    }
    final remoteUrl = this.remoteUrl?.trim();
    return remoteUrl != null && remoteUrl.isNotEmpty;
  }
}
