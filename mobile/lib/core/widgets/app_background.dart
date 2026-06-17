import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

class AppBackground extends StatelessWidget {
  const AppBackground({super.key, required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: const BoxDecoration(
        gradient: AppTheme.appBackgroundGradient,
      ),
      child: child,
    );
  }
}
