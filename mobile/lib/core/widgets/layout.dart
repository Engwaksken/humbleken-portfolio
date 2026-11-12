import 'package:flutter/material.dart';

/// Section heading with an optional eyebrow and trailing action.
/// Marked as a header for screen readers.
class SectionHeading extends StatelessWidget {
  const SectionHeading({super.key, this.eyebrow, required this.title, this.action, this.level = 2});

  final String? eyebrow;
  final String title;
  final Widget? action;

  /// 1 for a screen title, 2 for a section.
  final int level;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final style = level == 1 ? theme.textTheme.headlineMedium : theme.textTheme.titleLarge;
    final heading = Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (eyebrow != null && eyebrow!.trim().isNotEmpty)
          Padding(
            padding: const EdgeInsets.only(bottom: 4),
            child: Text(
              eyebrow!.toUpperCase(),
              style: theme.textTheme.labelLarge?.copyWith(color: theme.colorScheme.primary, letterSpacing: 1.1),
            ),
          ),
        Semantics(
          header: true,
          child: Text(title, style: style?.copyWith(fontWeight: FontWeight.w700)),
        ),
      ],
    );
    if (action == null) return heading;
    return Wrap(
      alignment: WrapAlignment.spaceBetween,
      crossAxisAlignment: WrapCrossAlignment.end,
      runSpacing: 4,
      spacing: 8,
      children: [heading, action!],
    );
  }
}

/// Lays children out one per row on phones and in two/three columns on wider
/// screens. Heights stay intrinsic so large text scaling never clips content.
class ResponsiveGrid extends StatelessWidget {
  const ResponsiveGrid({super.key, required this.children, this.spacing = 12, this.minItemWidth = 300});

  final List<Widget> children;
  final double spacing;
  final double minItemWidth;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final width = constraints.maxWidth;
        final columns = (width / minItemWidth).floor().clamp(1, 3);
        final itemWidth = (width - spacing * (columns - 1)) / columns;
        return Wrap(
          spacing: spacing,
          runSpacing: spacing,
          children: [for (final child in children) SizedBox(width: itemWidth, child: child)],
        );
      },
    );
  }
}

/// Centres content and caps its width on tablets/desktop web.
class ContentWidth extends StatelessWidget {
  const ContentWidth({super.key, required this.child, this.maxWidth = 960});

  final Widget child;
  final double maxWidth;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: maxWidth),
        child: child,
      ),
    );
  }
}

/// A numbered step badge used by hero cards and page steps.
class StepNumber extends StatelessWidget {
  const StepNumber(this.number, {super.key});
  final int number;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return ExcludeSemantics(
      child: Container(
        constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
        alignment: Alignment.center,
        decoration: BoxDecoration(color: scheme.primary, shape: BoxShape.circle),
        child: Text(
          '$number',
          style: Theme.of(context).textTheme.labelLarge?.copyWith(color: scheme.onPrimary, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }
}
