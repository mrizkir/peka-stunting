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
  });

  final Widget? title;
  final List<Widget>? actions;
  final Widget? leading;
  final bool automaticallyImplyLeading;
  final bool logoOnly;
  final double logoHeight;

  static Widget logo({double height = 32}) {
    return Image.asset(
      AppConfig.logoAssetPath,
      height: height,
      fit: BoxFit.contain,
    );
  }

  @override
  Widget build(BuildContext context) {
    return AppBar(
      leading: leading,
      automaticallyImplyLeading: automaticallyImplyLeading,
      title: logoOnly
          ? logo(height: logoHeight)
          : Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                logo(height: logoHeight),
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
