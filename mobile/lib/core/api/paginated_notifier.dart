import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'paginated.dart';

/// Accumulated pages of a paginated endpoint.
class PaginatedState<T> {
  const PaginatedState({required this.items, required this.meta, this.isLoadingMore = false, this.loadMoreError});

  final List<T> items;
  final PageMeta meta;
  final bool isLoadingMore;
  final Object? loadMoreError;

  bool get hasMore => meta.hasMore;

  PaginatedState<T> copyWith({
    List<T>? items,
    PageMeta? meta,
    bool? isLoadingMore,
    Object? loadMoreError,
    bool clearError = false,
  }) {
    return PaginatedState(
      items: items ?? this.items,
      meta: meta ?? this.meta,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
      loadMoreError: clearError ? null : (loadMoreError ?? this.loadMoreError),
    );
  }
}

/// Base notifier for infinite-scroll lists. Subclasses implement [fetchPage].
abstract class PaginatedNotifier<T> extends AsyncNotifier<PaginatedState<T>> {
  Future<Paginated<T>> fetchPage(int page);

  @override
  Future<PaginatedState<T>> build() async {
    final first = await fetchPage(1);
    return PaginatedState(items: first.items, meta: first.meta);
  }

  /// Loads the next page; no-op while loading, on error, or at the end.
  Future<void> loadMore() async {
    final current = state.value;
    if (current == null || state.isLoading || current.isLoadingMore || !current.hasMore) return;

    state = AsyncData(current.copyWith(isLoadingMore: true, clearError: true));
    try {
      final next = await fetchPage(current.meta.currentPage + 1);
      if (!ref.mounted) return;
      state = AsyncData(
        current.copyWith(
          items: [...current.items, ...next.items],
          meta: next.meta,
          isLoadingMore: false,
          clearError: true,
        ),
      );
    } catch (error) {
      if (!ref.mounted) return;
      state = AsyncData(current.copyWith(isLoadingMore: false, loadMoreError: error));
    }
  }

  /// Clears a failed "load more" so the list footer tries again.
  void retryLoadMore() {
    final current = state.value;
    if (current == null) return;
    state = AsyncData(current.copyWith(clearError: true));
    unawaited(loadMore());
  }
}
