import 'package:flutter/material.dart';

import '../api/api_exception.dart';

class LoadingView extends StatelessWidget {
  const LoadingView({super.key, this.label = 'Loading'});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Semantics(label: label, liveRegion: true, child: const CircularProgressIndicator()),
      ),
    );
  }
}

class ErrorView extends StatelessWidget {
  const ErrorView({super.key, required this.error, this.onRetry, this.compact = false});

  final Object error;
  final VoidCallback? onRetry;

  /// Compact variant for inline use (e.g. the footer of a list).
  final bool compact;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final message = ApiException.messageOf(error);
    final content = Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        if (!compact) ...[
          Icon(Icons.error_outline, size: 48, color: theme.colorScheme.error),
          const SizedBox(height: 12),
        ],
        Semantics(
          liveRegion: true,
          child: Text(message, textAlign: TextAlign.center, style: theme.textTheme.bodyLarge),
        ),
        if (onRetry != null) ...[
          const SizedBox(height: 12),
          FilledButton.tonalIcon(onPressed: onRetry, icon: const Icon(Icons.refresh), label: const Text('Try again')),
        ],
      ],
    );
    return Center(
      child: Padding(padding: EdgeInsets.all(compact ? 16 : 24), child: content),
    );
  }
}

class EmptyView extends StatelessWidget {
  const EmptyView({super.key, required this.message, this.icon = Icons.inbox_outlined});

  final String message;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ExcludeSemantics(child: Icon(icon, size: 48, color: theme.colorScheme.onSurfaceVariant)),
            const SizedBox(height: 12),
            Text(message, textAlign: TextAlign.center, style: theme.textTheme.bodyLarge),
          ],
        ),
      ),
    );
  }
}

/// Wraps a non-scrollable state view so [RefreshIndicator] still works.
class ScrollableFill extends StatelessWidget {
  const ScrollableFill({super.key, required this.child});
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) => SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: ConstrainedBox(
          constraints: BoxConstraints(minHeight: constraints.maxHeight),
          child: child,
        ),
      ),
    );
  }
}
