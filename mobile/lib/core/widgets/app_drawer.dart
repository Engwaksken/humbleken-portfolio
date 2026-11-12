import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/site/data/site_repository.dart';
import '../router/routes.dart';
import 'brand.dart';

/// "More" navigation: every destination, including the screens that are not
/// in the bottom bar (Approach, Booking, Contact, Assistant).
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
      child: SafeArea(
        child: ListView(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  BrandLogo(url: site.logoUrl, size: 48),
                  const SizedBox(height: 8),
                  Semantics(header: true, child: Text(site.brandName, style: theme.textTheme.titleLarge)),
                  if (site.tagline != null)
                    Text(
                      site.tagline!,
                      style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                    ),
                ],
              ),
            ),
            const Divider(),
            item(Icons.home_outlined, 'Home', Routes.home, tab: true),
            item(Icons.design_services_outlined, 'Services', Routes.services, tab: true),
            if (site.features.projects) item(Icons.work_outline, 'Work', Routes.work, tab: true),
            if (site.features.articles) item(Icons.article_outlined, 'Articles', Routes.articles, tab: true),
            const Divider(),
            item(Icons.route_outlined, 'Our approach', Routes.approach),
            item(Icons.event_available_outlined, 'Book a consultation', Routes.booking),
            item(Icons.mail_outline, 'Contact', Routes.contact),
            if (site.features.chatbot) item(Icons.smart_toy_outlined, 'AI assistant', Routes.assistant),
            const Divider(),
            item(Icons.person_outline, 'Account', Routes.account, tab: true),
          ],
        ),
      ),
    );
  }
}
