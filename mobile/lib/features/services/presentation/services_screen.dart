import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/widgets/brand.dart';
import '../../../core/widgets/layout.dart';
import '../../../core/widgets/state_views.dart';
import '../../pages/data/pages_repository.dart';
import '../../pages/presentation/page_content_view.dart';
import '../data/services_repository.dart';
import 'service_card.dart';

class ServicesScreen extends ConsumerWidget {
  const ServicesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final header = ref.watch(pageContentProvider('services'));
    final catalogue = ref.watch(serviceCatalogueProvider);
    final theme = Theme.of(context);

    Future<void> refresh() async {
      ref.invalidate(pageContentProvider('services'));
      try {
        ref.invalidate(serviceCatalogueProvider);
        await ref.read(serviceCatalogueProvider.future);
      } catch (_) {}
    }

    final children = <Widget>[
      // The page header is optional decoration; fall back to a plain title.
      if (header.value case final page?)
        PageContentView(page: page)
      else if (!header.isLoading)
        const SectionHeading(title: 'Services', level: 1),
      const SizedBox(height: 24),
    ];

    switch (catalogue) {
      case AsyncValue(:final value?):
        final groups = value.grouped();
        if (groups.isEmpty) {
          children.add(const EmptyView(message: 'No services are listed yet.'));
        }
        for (final group in groups) {
          children.addAll([
            SectionHeading(title: group.title),
            if (group.description != null)
              Padding(
                padding: const EdgeInsets.only(top: 4),
                child: Text(
                  group.description!,
                  style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                ),
              ),
            const SizedBox(height: 12),
            ResponsiveGrid(children: [for (final s in group.services) ServiceCard(service: s, showCategory: false)]),
            const SizedBox(height: 28),
          ]);
        }
      case AsyncValue(:final error?):
        children.add(ErrorView(error: error, onRetry: () => ref.invalidate(serviceCatalogueProvider)));
      default:
        children.add(const LoadingView(label: 'Loading services'));
    }

    return TopLevelScaffold(
      body: RefreshIndicator(
        onRefresh: refresh,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
          children: [
            ContentWidth(
              child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: children),
            ),
          ],
        ),
      ),
    );
  }
}
