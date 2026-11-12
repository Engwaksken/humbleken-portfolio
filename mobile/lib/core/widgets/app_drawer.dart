import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/site/data/site_repository.dart';
import '../config/app_config.dart';
import '../router/routes.dart';
import '../theme/app_theme.dart';
import 'brand.dart';

/// "More" navigation: every destination, including the screens that are not
/// in the bottom bar (Approach, Booking, Contact).
class AppDrawer extends ConsumerWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final site = ref.watch(siteConfigProvider);
    final theme = Theme.of(context);
    final location = GoRouterState.of(context).uri.path;

    Widget item(IconData icon, String label, String path, {bool tab = false}) {
      final selected = location == path || location.startsWith('$path/');
      return ListTile(
        leading: Icon(icon),
        title: Text(label),
        selected: selected,
        onTap: () {
          Navigator.of(context).pop();
          if (tab) {
            context.go(path);
          } else {
            context.push(path);
          }
        },
      );
    }

    return Drawer(
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          DecoratedBox(
            decoration: BoxDecoration(gradient: BrandTokens.of(context).heroGradient),
            child: SafeArea(
              bottom: false,
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // The logo already spells out the name, so it doubles as the heading.
                    Semantics(
                      header: true,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14)),
                        child: BrandLogo(url: site.logoUrl, size: 60, semanticLabel: AppConfig.appName),
                      ),
                    ),
                    if (site.tagline != null) ...[
                      const SizedBox(height: 14),
                      Text(
                        site.tagline!,
                        style: theme.textTheme.bodyMedium?.copyWith(color: Colors.white.withValues(alpha: 0.8)),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            child: Column(
              children: [
                item(Icons.home_outlined, 'Home', Routes.home, tab: true),
                item(Icons.design_services_outlined, 'Services', Routes.services, tab: true),
                if (site.features.projects) item(Icons.work_outline, 'Work', Routes.work, tab: true),
                if (site.features.articles) item(Icons.article_outlined, 'Articles', Routes.articles, tab: true),
                if (site.features.chatbot) item(Icons.smart_toy_outlined, 'Chatbot', Routes.assistant, tab: true),
                const Divider(),
                item(Icons.route_outlined, 'Our approach', Routes.approach),
                item(Icons.event_available_outlined, 'Book a consultation', Routes.booking),
                item(Icons.mail_outline, 'Contact', Routes.contact),
                const Divider(),
                item(Icons.person_outline, 'Account', Routes.account, tab: true),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
