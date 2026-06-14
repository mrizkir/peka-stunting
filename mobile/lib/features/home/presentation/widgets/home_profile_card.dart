import 'package:flutter/material.dart';

import '../../../../core/theme/app_theme.dart';
import '../../../../core/widgets/profile_avatar.dart';

class HomeProfileCard extends StatelessWidget {
  const HomeProfileCard({
    super.key,
    required this.name,
    required this.rolesLabel,
    this.profilePhotoUrl,
  });

  final String name;
  final String rolesLabel;
  final String? profilePhotoUrl;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppTheme.profileCardBackground,
        borderRadius: BorderRadius.circular(AppTheme.homeCardRadius),
      ),
      child: Row(
        children: [
          ProfileAvatar(
            name: name,
            profilePhotoUrl: profilePhotoUrl,
            radius: 28,
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Halo, $name',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: const Color(0xFF0F172A),
                      ),
                ),
                if (rolesLabel.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    rolesLabel,
                    style: TextStyle(
                      color: Colors.grey.shade700,
                      fontSize: 14,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
