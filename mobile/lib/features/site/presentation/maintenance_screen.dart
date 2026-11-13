import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/config/app_config.dart';
import '../../../core/widgets/brand.dart';
import '../data/site_models.dart';
import '../data/site_repository.dart';

/// Shown instead of the app while the site is in maintenance mode.
class MaintenanceScreen extends ConsumerWidget {
  const MaintenanceScreen({super.key, required this.site});

  final SiteConfig site;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final theme = Theme.of(context);
    final checking = ref.watch(siteProvider).isLoading;
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 480),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  BrandLogo(url: site.logoUrl, size: 64),
                  const SizedBox(height: 16),
                  ExcludeSemantics(child: Icon(Icons.construction, size: 56, color: theme.colorScheme.primary)),
                  const SizedBox(height: 16),
                  Semantics(
                    header: true,
                    child: Text(
                      '${AppConfig.appName} is under maintenance',
                      textAlign: TextAlign.center,
                      style: theme.textTheme.headlineSmall,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    site.maintenanceMessage ?? 'I am making some improvements and the site will be back shortly.',
                    textAlign: TextAlign.center,
                    style: theme.textTheme.bodyLarge,
                  ),
                  const SizedBox(height: 24),
                  FilledButton.icon(
                    onPressed: checking ? null : () => ref.invalidate(siteProvider),
                    icon: const Icon(Icons.refresh),
                    label: Text(checking ? 'Checking…' : 'Try again'),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
