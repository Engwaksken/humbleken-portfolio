import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

/// Horizontal padding used by every screen.
const kPagePadding = 20.0;

/// Section heading with an optional eyebrow and trailing action.
/// Marked as a header for screen readers.
class SectionHeading extends StatelessWidget {
  const SectionHeading({super.key, this.eyebrow, required this.title, this.action, this.level = 2, this.subtitle});

  final String? eyebrow;
  final String title;
  final String? subtitle;
  final Widget? action;

  /// 1 for a screen title, 2 for a section.
  final int level;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final style = level == 1 ? theme.textTheme.headlineMedium : theme.textTheme.headlineSmall;
    final heading = Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (eyebrow != null && eyebrow!.trim().isNotEmpty) ...[Eyebrow(eyebrow!), const SizedBox(height: 6)],
        Semantics(header: true, child: Text(title, style: style)),
        if (subtitle != null && subtitle!.trim().isNotEmpty) ...[
          const SizedBox(height: 6),
          Text(subtitle!, style: theme.textTheme.bodyMedium),
        ],
      ],
    );
    if (action == null) return heading;
    return Row(
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        Expanded(child: heading),
        const SizedBox(width: 8),
        action!,
      ],
    );
  }
}

/// Small uppercase label above a heading.
class Eyebrow extends StatelessWidget {
  const Eyebrow(this.text, {super.key, this.color});

  final String text;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Text(
      text.toUpperCase(),
      style: theme.textTheme.labelMedium?.copyWith(
        color: color ?? theme.colorScheme.primary,
        letterSpacing: 1.4,
        fontWeight: FontWeight.w700,
      ),
    );
  }
}

/// "See all" style link placed next to a section heading.
class SectionLink extends StatelessWidget {
  const SectionLink({super.key, required this.label, required this.onPressed});

  final String label;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return TextButton(
      onPressed: onPressed,
      style: TextButton.styleFrom(padding: const EdgeInsets.symmetric(horizontal: 8)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [Text(label), const SizedBox(width: 4), const Icon(Icons.arrow_forward_rounded, size: 18)],
      ),
    );
  }
}

/// Full-width branded header with a gradient background and white text, used
/// at the top of the home screen and content pages.
class GradientHeader extends StatelessWidget {
  const GradientHeader({
    super.key,
    this.eyebrow,
    required this.title,
    this.intro,
    this.introMaxLines = 4,
    this.actions = const [],
    this.bottomInset = 32,
  });

  final String? eyebrow;
  final String title;
  final String? intro;
  final int introMaxLines;
  final List<Widget> actions;

  /// Extra bottom padding, e.g. when a card overlaps the header.
  final double bottomInset;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final tokens = BrandTokens.of(context);
    return DecoratedBox(
      decoration: BoxDecoration(gradient: tokens.heroGradient),
      child: Padding(
        padding: EdgeInsets.fromLTRB(kPagePadding, 28, kPagePadding, bottomInset),
        child: ContentWidth(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (eyebrow != null && eyebrow!.trim().isNotEmpty) ...[
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(999),
                    border: Border.all(color: Colors.white.withValues(alpha: 0.18)),
                  ),
                  child: Text(
                    eyebrow!.toUpperCase(),
                    style: theme.textTheme.labelSmall?.copyWith(color: Colors.white, letterSpacing: 1.4),
                  ),
                ),
                const SizedBox(height: 16),
              ],
              Semantics(
                header: true,
                child: Text(title, style: theme.textTheme.headlineLarge?.copyWith(color: Colors.white, height: 1.15)),
              ),
              if (intro != null && intro!.trim().isNotEmpty) ...[
                const SizedBox(height: 14),
                ExpandableText(
                  intro!,
                  maxLines: introMaxLines,
                  style: theme.textTheme.bodyLarge?.copyWith(color: Colors.white.withValues(alpha: 0.88)),
                  linkColor: Colors.white,
                ),
              ],
              if (actions.isNotEmpty) ...[
                const SizedBox(height: 24),
                // Full-width stacked buttons on phones, side by side on wider screens.
                LayoutBuilder(
                  builder: (context, constraints) => constraints.maxWidth < 520
                      ? Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            for (final (i, action) in actions.indexed) ...[
                              if (i > 0) const SizedBox(height: 12),
                              action,
                            ],
                          ],
                        )
                      : Wrap(spacing: 12, runSpacing: 12, children: actions),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

/// Button styles for use on the gradient header.
ButtonStyle onBrandFilledStyle(BuildContext context) =>
    FilledButton.styleFrom(backgroundColor: Colors.white, foregroundColor: BrandTokens.of(context).deep);

ButtonStyle onBrandOutlinedStyle(BuildContext context) => OutlinedButton.styleFrom(
  foregroundColor: Colors.white,
  side: BorderSide(color: Colors.white.withValues(alpha: 0.6)),
);

/// Text that collapses to [maxLines] with a "Read more" toggle when longer.
class ExpandableText extends StatefulWidget {
  const ExpandableText(this.text, {super.key, this.maxLines = 4, this.style, this.linkColor});

  final String text;
  final int maxLines;
  final TextStyle? style;
  final Color? linkColor;

  @override
  State<ExpandableText> createState() => _ExpandableTextState();
}

class _ExpandableTextState extends State<ExpandableText> {
  bool _expanded = false;

  @override
  Widget build(BuildContext context) {
    final style = widget.style ?? DefaultTextStyle.of(context).style;
    return LayoutBuilder(
      builder: (context, constraints) {
        final painter = TextPainter(
          text: TextSpan(text: widget.text, style: style),
          maxLines: widget.maxLines,
          textDirection: Directionality.of(context),
          textScaler: MediaQuery.textScalerOf(context),
        )..layout(maxWidth: constraints.maxWidth);
        final overflows = painter.didExceedMaxLines;
        painter.dispose();

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              widget.text,
              style: style,
              maxLines: _expanded || !overflows ? null : widget.maxLines,
              overflow: _expanded || !overflows ? null : TextOverflow.ellipsis,
            ),
            if (overflows)
              TextButton(
                onPressed: () => setState(() => _expanded = !_expanded),
                style: TextButton.styleFrom(
                  foregroundColor: widget.linkColor ?? Theme.of(context).colorScheme.primary,
                  padding: EdgeInsets.zero,
                  alignment: Alignment.centerLeft,
                ),
                child: Text(_expanded ? 'Show less' : 'Read more'),
              ),
          ],
        );
      },
    );
  }
}

/// White rounded surface with the brand card shadow. Built on [Material] so
/// list tiles and ink effects inside it render correctly.
class SurfaceCard extends StatelessWidget {
  const SurfaceCard({super.key, required this.child, this.padding = const EdgeInsets.all(20), this.color});

  final Widget child;
  final EdgeInsetsGeometry padding;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final radius = BorderRadius.circular(AppTheme.radius);
    return DecoratedBox(
      decoration: BoxDecoration(borderRadius: radius, boxShadow: BrandTokens.of(context).cardShadow),
      child: Material(
        color: color ?? scheme.surfaceContainerLowest,
        shape: RoundedRectangleBorder(
          borderRadius: radius,
          side: BorderSide(color: scheme.outlineVariant.withValues(alpha: 0.7)),
        ),
        clipBehavior: Clip.antiAlias,
        child: Padding(padding: padding, child: child),
      ),
    );
  }
}

/// Rounded, tinted square holding an icon.
class IconBadge extends StatelessWidget {
  const IconBadge({super.key, required this.icon, this.size = 48, this.color, this.onDark = false});

  final IconData icon;
  final double size;
  final Color? color;
  final bool onDark;

  @override
  Widget build(BuildContext context) {
    final tint = color ?? Theme.of(context).colorScheme.primary;
    return ExcludeSemantics(
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          color: onDark ? Colors.white.withValues(alpha: 0.12) : tint.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(size * 0.3),
        ),
        child: Icon(icon, size: size * 0.5, color: onDark ? Colors.white : tint),
      ),
    );
  }
}

/// Lays children out one per row on phones and in two/three columns on wider
/// screens. Heights stay intrinsic so large text scaling never clips content.
class ResponsiveGrid extends StatelessWidget {
  const ResponsiveGrid({super.key, required this.children, this.spacing = 14, this.minItemWidth = 300});

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
    return ExcludeSemantics(
      child: Container(
        constraints: const BoxConstraints(minWidth: 34, minHeight: 34),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          gradient: BrandTokens.of(context).heroGradient,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Text(
          number.toString().padLeft(2, '0'),
          style: Theme.of(context).textTheme.labelLarge?.copyWith(color: Colors.white, fontWeight: FontWeight.w700),
        ),
      ),
    );
  }
}

/// Scrollable page: a full-width [header] followed by padded, width-capped
/// [children]. Used by content pages and detail screens.
class BrandedPageBody extends StatelessWidget {
  const BrandedPageBody({super.key, required this.header, required this.children, this.maxWidth = 960, this.onRefresh});

  final Widget header;
  final List<Widget> children;
  final double maxWidth;
  final Future<void> Function()? onRefresh;

  @override
  Widget build(BuildContext context) {
    final list = ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.only(bottom: 40),
      children: [
        header,
        Padding(
          padding: const EdgeInsets.fromLTRB(kPagePadding, 24, kPagePadding, 0),
          child: ContentWidth(
            maxWidth: maxWidth,
            child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: children),
          ),
        ),
      ],
    );
    if (onRefresh == null) return list;
    return RefreshIndicator(onRefresh: onRefresh!, child: list);
  }
}

/// App bar for pushed screens that open with a [GradientHeader], so the two
/// read as one continuous branded surface.
AppBar brandedAppBar(BuildContext context, {String? title, List<Widget>? actions}) {
  return AppBar(
    title: title == null ? null : Text(title),
    backgroundColor: BrandTokens.of(context).deep,
    foregroundColor: Colors.white,
    titleTextStyle: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.white),
    actions: actions,
  );
}
