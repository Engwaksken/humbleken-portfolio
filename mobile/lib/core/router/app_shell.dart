import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/site/data/site_repository.dart';

/// Bottom navigation around the shell branches. Work, Articles and Chatbot
/// are hidden when the site disables those features.
class AppShell extends ConsumerWidget {
  const AppShell({super.key, required this.shell});

  final StatefulNavigationShell shell;

  static const _destinations = [
    (branch: 0, icon: Icons.home_outlined, selected: Icons.home, label: 'Home'),
    (branch: 1, icon: Icons.design_services_outlined, selected: Icons.design_services, label: 'Services'),
    (branch: 5, icon: Icons.smart_toy_outlined, selected: Icons.smart_toy, label: 'Chatbot'),
    (branch: 2, icon: Icons.work_outline, selected: Icons.work, label: 'Work'),
    (branch: 3, icon: Icons.article_outlined, selected: Icons.article, label: 'Articles'),
    (branch: 4, icon: Icons.person_outline, selected: Icons.person, label: 'Account'),
  ];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final features = ref.watch(siteConfigProvider.select((s) => s.features));
    final visible = _destinations
        .where(
          (d) =>
              (d.branch != 2 || features.projects) &&
              (d.branch != 3 || features.articles) &&
              (d.branch != 5 || features.chatbot),
        )
        .toList();

    final selectedIndex = visible.indexWhere((d) => d.branch == shell.currentIndex);

    return Scaffold(
      body: shell,
      bottomNavigationBar: NavigationBar(
        selectedIndex: selectedIndex < 0 ? 0 : selectedIndex,
        onDestinationSelected: (index) {
          final branch = visible[index].branch;
          // Re-selecting the current tab returns it to its root.
          shell.goBranch(branch, initialLocation: branch == shell.currentIndex);
        },
        destinations: [
          for (final d in visible)
            NavigationDestination(icon: Icon(d.icon), selectedIcon: Icon(d.selected), label: d.label),
        ],
      ),
    );
  }
}
