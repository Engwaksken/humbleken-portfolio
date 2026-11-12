import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/widgets/brand.dart';
import '../../../core/widgets/layout.dart';
import '../../../core/widgets/paginated_list_view.dart';
import '../data/article_models.dart';
import '../data/articles_repository.dart';
import 'article_card.dart';

class ArticlesScreen extends ConsumerStatefulWidget {
  const ArticlesScreen({super.key});

  @override
  ConsumerState<ArticlesScreen> createState() => _ArticlesScreenState();
}

class _ArticlesScreenState extends ConsumerState<ArticlesScreen> {
  final _search = TextEditingController();
  Timer? _debounce;
  ArticleQuery _query = const ArticleQuery();

  @override
  void dispose() {
    _debounce?.cancel();
    _search.dispose();
    super.dispose();
  }

  void _onSearchChanged(String text) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 450), () => _applySearch(text));
  }

  void _applySearch(String text) {
    _debounce?.cancel();
    final q = text.trim();
    if (q != _query.q) setState(() => _query = ArticleQuery(q: q, category: _query.category));
  }

  void _selectCategory(String? slug) {
    setState(() => _query = ArticleQuery(q: _query.q, category: slug));
  }

  @override
  Widget build(BuildContext context) {
    final provider = articlesProvider(_query);
    final notifier = ref.read(provider.notifier);

    return TopLevelScaffold(
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
            child: ContentWidth(
              child: TextField(
                controller: _search,
                textInputAction: TextInputAction.search,
                onChanged: _onSearchChanged,
                onSubmitted: _applySearch,
                decoration: InputDecoration(
                  labelText: 'Search articles',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: ListenableBuilder(
                    listenable: _search,
                    builder: (context, _) => _search.text.isEmpty
                        ? const SizedBox.shrink()
                        : IconButton(
                            tooltip: 'Clear search',
                            icon: const Icon(Icons.clear),
                            onPressed: () {
                              _search.clear();
                              _applySearch('');
                            },
                          ),
                  ),
                ),
              ),
            ),
          ),
          _CategoryChips(selected: _query.category, onSelected: _selectCategory),
          Expanded(
            child: PaginatedListView(
              value: ref.watch(provider),
              emptyMessage: _query.q.isEmpty && _query.category == null
                  ? 'No articles have been published yet.'
                  : 'No articles match your search.',
              itemBuilder: (context, article) => ContentWidth(child: ArticleCard(article: article)),
              onLoadMore: notifier.loadMore,
              onRetryLoadMore: notifier.retryLoadMore,
              onRefresh: () async {
                ref.invalidate(articleCategoriesProvider);
                try {
                  ref.invalidate(provider);
                  await ref.read(provider.future);
                } catch (_) {}
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _CategoryChips extends ConsumerWidget {
  const _CategoryChips({required this.selected, required this.onSelected});

  final String? selected;
  final ValueChanged<String?> onSelected;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final categories = ref.watch(articleCategoriesProvider).value ?? const [];
    if (categories.isEmpty) return const SizedBox(height: 8);

    return Semantics(
      container: true,
      label: 'Filter by category',
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: Row(
          children: [
            ChoiceChip(label: const Text('All'), selected: selected == null, onSelected: (_) => onSelected(null)),
            for (final category in categories) ...[
              const SizedBox(width: 8),
              ChoiceChip(
                label: Text(category.name),
                selected: selected == category.slug,
                onSelected: (on) => onSelected(on ? category.slug : null),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
