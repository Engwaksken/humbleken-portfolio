import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../api/paginated_notifier.dart';
import 'state_views.dart';

/// Infinite-scroll list with pull-to-refresh and loading/empty/error states.
///
/// Loading the next page is triggered when the footer becomes visible, which
/// also covers the case of a first page too short to scroll.
class PaginatedListView<T> extends StatelessWidget {
  const PaginatedListView({
    super.key,
    required this.value,
    required this.itemBuilder,
    required this.onRefresh,
    required this.onLoadMore,
    required this.onRetryLoadMore,
    required this.emptyMessage,
    this.header,
    this.padding = const EdgeInsets.fromLTRB(16, 8, 16, 24),
    this.separator = const SizedBox(height: 12),
  });

  final AsyncValue<PaginatedState<T>> value;
  final Widget Function(BuildContext context, T item) itemBuilder;
  final Future<void> Function() onRefresh;
  final VoidCallback onLoadMore;
  final VoidCallback onRetryLoadMore;
  final String emptyMessage;

  /// Optional widget shown above the items (scrolls with the list).
  final Widget? header;
  final EdgeInsets padding;
  final Widget separator;

  @override
  Widget build(BuildContext context) {
    final data = value.value;

    Widget body;
    if (data == null && value.hasError) {
      body = _withHeader(ErrorView(error: value.error!, onRetry: onRefresh));
    } else if (data == null) {
      body = _withHeader(const LoadingView(label: 'Loading list'));
    } else if (data.items.isEmpty) {
      body = _withHeader(EmptyView(message: emptyMessage));
    } else {
      final showFooter = data.hasMore || data.loadMoreError != null;
      final count = data.items.length + (header != null ? 1 : 0) + (showFooter ? 1 : 0);
      body = ListView.separated(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: padding,
        itemCount: count,
        separatorBuilder: (_, _) => separator,
        itemBuilder: (context, index) {
          if (header != null) {
            if (index == 0) return header!;
            index -= 1;
          }
          if (index < data.items.length) return itemBuilder(context, data.items[index]);
          return _Footer(state: data, onLoadMore: onLoadMore, onRetry: onRetryLoadMore);
        },
      );
    }

    return RefreshIndicator(onRefresh: onRefresh, child: body);
  }

  Widget _withHeader(Widget state) {
    if (header == null) return ScrollableFill(child: state);
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: padding,
      children: [header!, const SizedBox(height: 24), state],
    );
  }
}

class _Footer extends StatelessWidget {
  const _Footer({required this.state, required this.onLoadMore, required this.onRetry});

  final PaginatedState<Object?> state;
  final VoidCallback onLoadMore;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    if (state.loadMoreError != null) {
      return ErrorView(error: state.loadMoreError!, onRetry: onRetry, compact: true);
    }
    if (!state.isLoadingMore && state.hasMore) {
      WidgetsBinding.instance.addPostFrameCallback((_) => onLoadMore());
    }
    return const LoadingView(label: 'Loading more');
  }
}
