import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/config/app_config.dart';
import 'core/push/push_listener.dart';
import 'core/router/app_router.dart';
import 'core/theme/app_theme.dart';
import 'core/widgets/state_views.dart';
import 'features/site/data/site_repository.dart';
import 'features/site/presentation/maintenance_screen.dart';

enum _StartupMode { loading, maintenance, ready }

class PortfolioApp extends ConsumerWidget {
  const PortfolioApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);
    final siteAsync = ref.watch(siteProvider);
    final site = ref.watch(siteConfigProvider);

    // First launch waits for /site so the brand theme is applied up front.
    // On failure the app continues with the fallback theme and features.
    final mode = (!siteAsync.hasValue && !siteAsync.hasError)
        ? _StartupMode.loading
        : site.maintenanceEnabled
        ? _StartupMode.maintenance
        : _StartupMode.ready;

    return MaterialApp.router(
      title: AppConfig.appName,
      debugShowCheckedModeBanner: false,
      scaffoldMessengerKey: rootMessengerKey,
      theme: AppTheme.light(primaryHex: site.primaryHex, secondaryHex: site.secondaryHex),
      darkTheme: AppTheme.dark(primaryHex: site.primaryHex),
      routerConfig: router,
      builder: (context, child) {
        if (mode == _StartupMode.ready) return PushListener(child: child ?? const SizedBox.shrink());
        // Builder content sits above the Navigator, so give it an Overlay for
        // tooltips, ink and text selection.
        return Overlay(
          key: ValueKey(mode),
          initialEntries: [OverlayEntry(builder: (_) => const _StartupGate())],
        );
      },
    );
  }
}

class _StartupGate extends ConsumerWidget {
  const _StartupGate();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final site = ref.watch(siteConfigProvider);
    if (site.maintenanceEnabled) return MaintenanceScreen(site: site);
    return const Scaffold(body: LoadingView(label: 'Starting'));
  }
}
