import 'package:flutter/material.dart';

class ProfileAvatar extends StatefulWidget {
  const ProfileAvatar({
    super.key,
    required this.name,
    this.profilePhotoUrl,
    this.radius = 28,
    this.onTap,
    this.showEditBadge = false,
  });

  final String name;
  final String? profilePhotoUrl;
  final double radius;
  final VoidCallback? onTap;
  final bool showEditBadge;

  @override
  State<ProfileAvatar> createState() => _ProfileAvatarState();
}

class _ProfileAvatarState extends State<ProfileAvatar> {
  bool _imageFailed = false;

  @override
  void didUpdateWidget(covariant ProfileAvatar oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.profilePhotoUrl != widget.profilePhotoUrl) {
      _imageFailed = false;
      if (oldWidget.profilePhotoUrl != null) {
        NetworkImage(oldWidget.profilePhotoUrl!).evict();
      }
    }
  }

  String get _initials {
    final parts = widget.name.trim().split(RegExp(r'\s+'));
    if (parts.isEmpty || parts.first.isEmpty) {
      return 'P';
    }
    if (parts.length == 1) {
      return parts.first[0].toUpperCase();
    }
    return '${parts.first[0]}${parts.last[0]}'.toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    final showImage =
        widget.profilePhotoUrl != null && !_imageFailed;

    final avatar = CircleAvatar(
      key: ValueKey(widget.profilePhotoUrl),
      radius: widget.radius,
      backgroundColor: Colors.white,
      backgroundImage: showImage
          ? NetworkImage(widget.profilePhotoUrl!)
          : null,
      onBackgroundImageError: showImage
          ? (_, _) {
              if (mounted) {
                setState(() => _imageFailed = true);
              }
            }
          : null,
      child: showImage
          ? null
          : Text(
              _initials,
              style: TextStyle(
                fontSize: widget.radius * 0.72,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF0F172A),
              ),
            ),
    );

    Widget content = avatar;

    if (widget.showEditBadge) {
      content = Stack(
        clipBehavior: Clip.none,
        children: [
          avatar,
          Positioned(
            right: 0,
            bottom: 0,
            child: DecoratedBox(
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.primary,
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white, width: 2),
              ),
              child: const Padding(
                padding: EdgeInsets.all(4),
                child: Icon(
                  Icons.camera_alt,
                  size: 16,
                  color: Colors.white,
                ),
              ),
            ),
          ),
        ],
      );
    }

    if (widget.onTap == null) {
      return content;
    }

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: widget.onTap,
        customBorder: const CircleBorder(),
        child: content,
      ),
    );
  }
}
