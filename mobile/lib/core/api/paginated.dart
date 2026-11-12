import '../utils/json.dart';

/// Laravel pagination `meta` block.
class PageMeta {
  const PageMeta({required this.currentPage, required this.lastPage, required this.perPage, required this.total});

  final int currentPage;
  final int lastPage;
  final int perPage;
  final int total;

  bool get hasMore => currentPage < lastPage;

  factory PageMeta.fromJson(Json json) {
    final current = asInt(json['current_page'], 1);
    return PageMeta(
      currentPage: current,
      lastPage: asInt(json['last_page'], current),
      perPage: asInt(json['per_page']),
      total: asInt(json['total']),
    );
  }
}

/// One page of a paginated collection: `{"data": [...], "meta": {...}}`.
class Paginated<T> {
  const Paginated({required this.items, required this.meta});

  final List<T> items;
  final PageMeta meta;

  factory Paginated.fromJson(Json json, T Function(Json json) parse) {
    final items = asList(json['data'], parse);
    final metaJson = asMapOrNull(json['meta']);
    return Paginated(
      items: items,
      meta: metaJson != null
          ? PageMeta.fromJson(metaJson)
          : PageMeta(currentPage: 1, lastPage: 1, perPage: items.length, total: items.length),
    );
  }
}
