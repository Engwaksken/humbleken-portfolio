import 'package:flutter/material.dart';

/// A card that navigates when tapped. Its content is merged into a single
/// semantics node so screen readers announce it as one link.
class TappableCard extends StatelessWidget {
  const TappableCard({super.key, required this.onTap, required this.child, this.color});

  final VoidCallback onTap;
  final Widget child;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.zero,
      color: color,
      child: MergeSemantics(
        child: Semantics(
          link: true,
          child: InkWell(onTap: onTap, child: child),
        ),
      ),
    );
  }
}

/// Small non-interactive label (technology, tag, status).
class InfoPill extends StatelessWidget {
  const InfoPill({super.key, required this.label, this.background, this.foreground, this.icon});

  final String label;
  final Color? background;
  final Color? foreground;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final fg = foreground ?? scheme.onSecondaryContainer;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: background ?? scheme.secondaryContainer,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[ExcludeSemantics(child: Icon(icon, size: 16, color: fg)), const SizedBox(width: 4)],
          Flexible(
            child: Text(label, style: Theme.of(context).textTheme.labelMedium?.copyWith(color: fg)),
          ),
        ],
      ),
    );
  }
}
