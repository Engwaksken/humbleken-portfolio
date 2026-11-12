import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/paginated.dart';
import '../../../core/api/paginated_notifier.dart';
import '../../../core/utils/json.dart';
import 'article_models.dart';

class ArticlesRepository {
  ArticlesRepository(this._api);
  final ApiClient _api;

  Future<Paginated<ArticleSummary>> list({int page = 1, ArticleQuery query = const ArticleQuery()}) async {
    final json = await _api.get('articles', query: {'page': page, 'q': query.q.trim(), 'category': query.category});
    return Paginated.fromJson(json, ArticleSummary.fromJson);
  }

  Future<ArticleDetail> detail(String slug) async {
    final json = await _api.get('articles/${Uri.encodeComponent(slug)}');
    return ArticleDetail.fromJson(asMap(json['data']));
  }

  Future<List<Taxon>> categories() async {
    final json = await _api.get('article-categories');
    return asList(json['data'], Taxon.fromJson);
  }
}

final articlesRepositoryProvider = Provider<ArticlesRepository>(
  (ref) => ArticlesRepository(ref.watch(apiClientProvider)),
);

class ArticlesNotifier extends PaginatedNotifier<ArticleSummary> {
  ArticlesNotifier(this.query);
  final ArticleQuery query;

  @override
  Future<Paginated<ArticleSummary>> fetchPage(int page) =>
      ref.read(articlesRepositoryProvider).list(page: page, query: query);
}

final articlesProvider = AsyncNotifierProvider.autoDispose
    .family<ArticlesNotifier, PaginatedState<ArticleSummary>, ArticleQuery>(ArticlesNotifier.new);

final articleCategoriesProvider = FutureProvider<List<Taxon>>(
  (ref) => ref.watch(articlesRepositoryProvider).categories(),
);

final articleDetailProvider = FutureProvider.autoDispose.family<ArticleDetail, String>(
  (ref, slug) => ref.watch(articlesRepositoryProvider).detail(slug),
);
