import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class AppShell extends StatelessWidget {
  const AppShell({
    super.key,
    required this.navigationShell,
  });

  final StatefulNavigationShell navigationShell;

  @override
  Widget build(BuildContext context) {
    final router = GoRouter.of(context);

    return ListenableBuilder(
      listenable: router.routerDelegate,
      builder: (context, _) {
        final colorScheme = Theme.of(context).colorScheme;
        final navTheme = NavigationBarTheme.of(context);
        final indicatorColor =
            navTheme.indicatorColor ?? colorScheme.secondaryContainer;
        final location = router.state.matchedLocation;
        final isHomeSelected = location == '/';
        final isProfileSelected = location == '/profile';

        return PopScope(
          canPop: navigationShell.currentIndex == 0,
          onPopInvokedWithResult: (didPop, result) {
            if (!didPop) {
              navigationShell.goBranch(0, initialLocation: false);
            }
          },
          child: Scaffold(
            body: navigationShell,
            bottomNavigationBar: Material(
              color: navTheme.backgroundColor ?? colorScheme.surfaceContainer,
              elevation: navTheme.elevation ?? 3,
              shadowColor: navTheme.shadowColor ?? Colors.black26,
              child: SafeArea(
                top: false,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    _NavIconButton(
                      selected: isHomeSelected,
                      indicatorColor: indicatorColor,
                      icon: Icons.home_outlined,
                      selectedIcon: Icons.home,
                      tooltip: 'Home',
                      onTap: () => navigationShell.goBranch(0, initialLocation: true),
                    ),
                    _NavIconButton(
                      selected: isProfileSelected,
                      indicatorColor: indicatorColor,
                      icon: Icons.person_outline,
                      selectedIcon: Icons.person,
                      tooltip: 'Profil',
                      onTap: () => navigationShell.goBranch(
                        1,
                        initialLocation: navigationShell.currentIndex == 1,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}

class _NavIconButton extends StatelessWidget {
  const _NavIconButton({
    required this.selected,
    required this.indicatorColor,
    required this.icon,
    required this.selectedIcon,
    required this.tooltip,
    required this.onTap,
  });

  final bool selected;
  final Color indicatorColor;
  final IconData icon;
  final IconData selectedIcon;
  final String tooltip;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;

    return IconButton(
      tooltip: tooltip,
      onPressed: onTap,
      padding: EdgeInsets.zero,
      constraints: const BoxConstraints(minWidth: 64, minHeight: 48),
      icon: Container(
        width: 64,
        height: 32,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: selected ? indicatorColor : Colors.transparent,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Icon(
          selected ? selectedIcon : icon,
          size: 24,
          color: selected
              ? colorScheme.onSecondaryContainer
              : colorScheme.onSurfaceVariant,
        ),
      ),
    );
  }
}
