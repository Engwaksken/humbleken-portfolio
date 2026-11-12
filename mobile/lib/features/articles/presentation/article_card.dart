import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/routes.dart';
import '../../../core/utils/format.dart';
import '../../../core/widgets/tappable_card.dart';
import '../data/article_models.dart';

/// "12 Nov 2026 · 5 min read"
String articleMetaLine(ArticleSummary article) {
  final parts = [
    if (article.publishedAt != null) formatDate(article.publishedAt),
    if (article.readingMinutes != null && article.readingMinutes! > 0) '${article.readingMinutes} min read',
  ];
  return parts.join(' · ');
}

class ArticleCard extends StatelessWidget {
  const ArticleCard({super.key, required this.article});

  final ArticleSummary article;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final meta = articleMetaLine(article);
    return TappableCard(
      onTap: () => context.push(Routes.article(article.slug)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (article.category != null)
              Text(
                article.category!.name,
                style: theme.textTheme.labelMedium?.copyWith(color: theme.colorScheme.primary),
              ),
            Text(article.title, style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
            if (meta.isNotEmpty)
              Text(meta, style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.onSurfaceVariant)),
            if (article.excerpt != null) ...[
              const SizedBox(height: 6),
              Text(article.excerpt!, maxLines: 4, overflow: TextOverflow.ellipsis),
            ],
          ],
        ),
      ),
    );
  }
}
