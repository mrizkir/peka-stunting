import 'package:flutter/material.dart';

import '../../../../core/theme/app_theme.dart';

class HomeColoredMenuCard extends StatelessWidget {
  const HomeColoredMenuCard({
    super.key,
    this.icon,
    this.imageAsset,
    required this.title,
    required this.subtitle,
    required this.backgroundColor,
    required this.onTap,
    this.foregroundColor = Colors.white,
  }) : assert(icon != null || imageAsset != null);

  final IconData? icon;
  final String? imageAsset;
  final String title;
  final String subtitle;
  final Color backgroundColor;
  final Color foregroundColor;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final subtitleColor = foregroundColor == Colors.white
        ? foregroundColor.withValues(alpha: 0.9)
        : Colors.grey.shade700;

    return Material(
      color: backgroundColor,
      elevation: 0,
      borderRadius: BorderRadius.circular(AppTheme.homeCardRadius),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Row(
            children: [
              if (imageAsset != null)
                SizedBox(
                  width: 56,
                  height: 56,
                  child: Image.asset(imageAsset!, fit: BoxFit.contain),
                )
              else
                Container(
                  width: 56,
                  height: 56,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(icon, color: foregroundColor, size: 28),
                ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.w700,
                        color: foregroundColor,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      style: TextStyle(
                        color: subtitleColor,
                        height: 1.4,
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(Icons.chevron_right, color: foregroundColor),
            ],
          ),
        ),
      ),
    );
  }
}
