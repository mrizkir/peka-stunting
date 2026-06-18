import 'package:flutter/material.dart';

import '../config/app_config.dart';

class PekaAppBar extends StatelessWidget implements PreferredSizeWidget {
  const PekaAppBar({
    super.key,
    this.title,
    this.actions,
    this.leading,
    this.automaticallyImplyLeading = true,
    this.logoOnly = false,
    this.logoHeight = 32,
    this.logoAssetPath,
  });

  final Widget? title;
  final List<Widget>? actions;
  final Widget? leading;
  final bool automaticallyImplyLeading;
  final bool logoOnly;
  final double logoHeight;
  final String? logoAssetPath;

  static Widget logo({double height = 32, String? assetPath}) {
    return Image.asset(
      assetPath ?? AppConfig.logoAssetPath,
      height: height,
      fit: BoxFit.contain,
    );
  }

  @override
  Widget build(BuildContext context) {
    return AppBar(
      leading: leading,
      automaticallyImplyLeading: automaticallyImplyLeading,
      titleSpacing: 0,
      title: logoOnly
          ? logo(height: logoHeight, assetPath: logoAssetPath)
          : Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                logo(height: logoHeight, assetPath: logoAssetPath),
                if (title != null) ...[
                  const SizedBox(width: 10),
                  Flexible(child: title!),
                ],
              ],
            ),
      actions: actions,
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);
}
