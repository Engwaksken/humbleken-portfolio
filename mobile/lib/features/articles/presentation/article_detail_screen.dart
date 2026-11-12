import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/widgets/layout.dart';
import '../../../core/widgets/markdown_view.dart';
import '../../../core/widgets/state_views.dart';
import '../../../core/widgets/tappable_card.dart';
import '../data/article_models.dart';
import '../data/articles_repository.dart';
import 'article_card.dart';

class ArticleDetailScreen extends ConsumerWidget {
  const ArticleDetailScreen({super.key, required this.slug});

  final String slug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final article = ref.watch(articleDetailProvider(slug));
    return Scaffold(
      appBar: brandedAppBar(context),
      body: switch (article) {
        AsyncValue(:final value?) => _ArticleBody(article: value),
        AsyncValue(:final error?) => ErrorView(
          error: error,
          onRetry: () => ref.invalidate(articleDetailProvider(slug)),
        ),
        _ => const LoadingView(label: 'Loading article'),
      },
    );
  }
}

class _ArticleBody extends StatelessWidget {
  const _ArticleBody({required this.article});

  final ArticleDetail article;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final meta = articleMetaLine(article);
    return BrandedPageBody(
      maxWidth: 760,
      header: GradientHeader(
        eyebrow: [article.category?.name, if (meta.isNotEmpty) meta].whereType<String>().join(' · '),
        title: article.title,
      ),
      children: [
        if (article.tags.isNotEmpty) ...[
          Semantics(
            label: 'Tags: ${article.tags.map((t) => t.name).join(', ')}',
            excludeSemantics: true,
            child: Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [for (final tag in article.tags) InfoPill(label: '#${tag.name}')],
            ),
          ),
        ],
        const SizedBox(height: 20),
        if (article.bodyMarkdown != null)
          MarkdownView(data: article.bodyMarkdown!)
        else if (article.excerpt != null)
          Text(article.excerpt!, style: theme.textTheme.bodyLarge),
      ],
    );
  }
}
