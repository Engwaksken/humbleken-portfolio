import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/widgets/layout.dart';
import '../../../core/widgets/state_views.dart';
import '../data/pages_repository.dart';
import 'page_content_view.dart';

/// Pushed screen for a CMS page (`/pages/{key}`), e.g. Approach.
class GenericPageScreen extends ConsumerWidget {
  const GenericPageScreen({super.key, required this.pageKey, required this.fallbackTitle});

  final String pageKey;
  final String fallbackTitle;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final page = ref.watch(pageContentProvider(pageKey));
    return Scaffold(
      appBar: AppBar(title: Text(page.value?.eyebrow ?? fallbackTitle)),
      body: switch (page) {
        AsyncValue(:final value?) => RefreshIndicator(
          onRefresh: () async {
            try {
              ref.invalidate(pageContentProvider(pageKey));
              await ref.read(pageContentProvider(pageKey).future);
            } catch (_) {}
          },
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
            children: [ContentWidth(child: PageContentView(page: value))],
          ),
        ),
        AsyncValue(:final error?) => ErrorView(
          error: error,
          onRetry: () => ref.invalidate(pageContentProvider(pageKey)),
        ),
        _ => const LoadingView(),
      },
    );
  }
}
