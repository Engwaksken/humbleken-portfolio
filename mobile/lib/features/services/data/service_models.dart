import '../../../core/utils/json.dart';

class ServiceCategory {
  const ServiceCategory({required this.id, required this.name, required this.slug, this.description});

  final int id;
  final String name;
  final String slug;
  final String? description;

  factory ServiceCategory.fromJson(Json json) => ServiceCategory(
    id: asInt(json['id']),
    name: asString(json['name']),
    slug: asString(json['slug']),
    description: asStringOrNull(json['description']),
  );
}

class ServiceSummary {
  const ServiceSummary({
    required this.id,
    required this.slug,
    required this.name,
    this.summary,
    this.category,
    this.imageUrl,
  });

  final int id;
  final String slug;
  final String name;
  final String? summary;
  final ServiceCategory? category;
  final String? imageUrl;

  factory ServiceSummary.fromJson(Json json) {
    final category = asMapOrNull(json['category']);
    return ServiceSummary(
      id: asInt(json['id']),
      slug: asString(json['slug']),
      name: asString(json['name']),
      summary: asStringOrNull(json['summary']),
      category: category == null ? null : ServiceCategory.fromJson(category),
      imageUrl: asStringOrNull(json['image_url']),
    );
  }
}

class ServiceDetail extends ServiceSummary {
  const ServiceDetail({
    required super.id,
    required super.slug,
    required super.name,
    super.summary,
    super.category,
    super.imageUrl,
    this.description,
    this.deliveryMethod,
    this.estimatedDuration,
    this.indicativePrice,
    this.videoEmbedUrl,
  });

  final String? description;
  final String? deliveryMethod;
  final String? estimatedDuration;

  /// Price in the site currency, ready to show (e.g. "UGX 250,000").
  final String? indicativePrice;
  final String? videoEmbedUrl;

  factory ServiceDetail.fromJson(Json json) {
    final base = ServiceSummary.fromJson(json);
    return ServiceDetail(
      id: base.id,
      slug: base.slug,
      name: base.name,
      summary: base.summary,
      category: base.category,
      imageUrl: base.imageUrl,
      description: asStringOrNull(json['description']),
      deliveryMethod: asStringOrNull(json['delivery_method']),
      estimatedDuration: asStringOrNull(json['estimated_duration']),
      // Older servers only send the raw amount.
      indicativePrice: asStringOrNull(json['indicative_price_formatted']) ?? asStringOrNull(json['indicative_price']),
      videoEmbedUrl: asStringOrNull(json['video_embed_url']),
    );
  }
}

/// `GET /services`: every active service plus the active categories.
class ServiceCatalogue {
  const ServiceCatalogue({required this.services, required this.categories});

  final List<ServiceSummary> services;
  final List<ServiceCategory> categories;

  factory ServiceCatalogue.fromJson(Json json) => ServiceCatalogue(
    services: asList(json['data'], ServiceSummary.fromJson),
    categories: asList(json['categories'], ServiceCategory.fromJson),
  );

  /// Services grouped by category in category order; uncategorised (or in a
  /// category not listed) last under "Other services".
  List<({String title, String? description, List<ServiceSummary> services})> grouped() {
    final groups = <({String title, String? description, List<ServiceSummary> services})>[];
    final placed = <int>{};
    for (final category in categories) {
      final members = services.where((s) => s.category?.id == category.id).toList();
      if (members.isEmpty) continue;
      placed.addAll(members.map((s) => s.id));
      groups.add((title: category.name, description: category.description, services: members));
    }
    final rest = services.where((s) => !placed.contains(s.id)).toList();
    if (rest.isNotEmpty) {
      groups.add((title: groups.isEmpty ? 'Services' : 'Other services', description: null, services: rest));
    }
    return groups;
  }
}
