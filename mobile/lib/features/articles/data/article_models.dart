import '../../../core/utils/json.dart';

/// A `{name, slug}` pair used for article categories and tags.
class Taxon {
  const Taxon({required this.name, required this.slug});

  final String name;
  final String slug;

  factory Taxon.fromJson(Json json) => Taxon(name: asString(json['name']), slug: asString(json['slug']));
}

class ArticleSummary {
  const ArticleSummary({
    required this.id,
    required this.slug,
    required this.title,
    this.excerpt,
    this.category,
    this.tags = const [],
    this.publishedAt,
    this.readingMinutes,
  });

  final int id;
  final String slug;
  final String title;
  final String? excerpt;
  final Taxon? category;
  final List<Taxon> tags;
  final DateTime? publishedAt;
  final int? readingMinutes;

  factory ArticleSummary.fromJson(Json json) {
    final category = asMapOrNull(json['category']);
    return ArticleSummary(
      id: asInt(json['id']),
      slug: asString(json['slug']),
      title: asString(json['title']),
      excerpt: asStringOrNull(json['excerpt']),
      category: category == null ? null : Taxon.fromJson(category),
      tags: asList(json['tags'], Taxon.fromJson),
      publishedAt: asDateTime(json['published_at']),
      readingMinutes: asIntOrNull(json['reading_minutes']),
    );
  }
}

class ArticleDetail extends ArticleSummary {
  const ArticleDetail({
    required super.id,
    required super.slug,
    required super.title,
    super.excerpt,
    super.category,
    super.tags,
    super.publishedAt,
    super.readingMinutes,
    this.bodyMarkdown,
  });

  final String? bodyMarkdown;

  factory ArticleDetail.fromJson(Json json) {
    final base = ArticleSummary.fromJson(json);
    return ArticleDetail(
      id: base.id,
      slug: base.slug,
      title: base.title,
      excerpt: base.excerpt,
      category: base.category,
      tags: base.tags,
      publishedAt: base.publishedAt,
      readingMinutes: base.readingMinutes,
      bodyMarkdown: asStringOrNull(json['body_markdown']),
    );
  }
}

/// Filters for `GET /articles`.
class ArticleQuery {
  const ArticleQuery({this.q = '', this.category});

  final String q;
  final String? category;

  @override
  bool operator ==(Object other) => other is ArticleQuery && other.q == q && other.category == category;

  @override
  int get hashCode => Object.hash(q, category);
}
