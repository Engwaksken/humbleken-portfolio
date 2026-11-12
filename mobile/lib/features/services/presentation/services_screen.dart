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

    Future<void> refresh() async {
      ref.invalidate(pageContentProvider('services'));
      try {
        ref.invalidate(serviceCatalogueProvider);
        await ref.read(serviceCatalogueProvider.future);
      } catch (_) {}
    }

    final page = header.value;
    final children = <Widget>[if (page != null) PageContentView(page: page)];

    switch (catalogue) {
      case AsyncValue(:final value?):
        final groups = value.grouped();
        if (groups.isEmpty) {
          children.add(const EmptyView(message: 'No services are listed yet.'));
        }
        for (final group in groups) {
          children.addAll([
            SectionHeading(title: group.title, subtitle: group.description),
            const SizedBox(height: 16),
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
      onBrand: true,
      body: BrandedPageBody(
        onRefresh: refresh,
        // The page header is optional decoration; fall back to a plain title.
        header: page != null ? PageHeader(page: page) : const GradientHeader(title: 'Services'),
        children: children,
      ),
    );
  }
}
