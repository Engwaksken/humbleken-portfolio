import 'package:flutter/material.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_network_image.dart';
import '../../../core/widgets/image_viewer.dart';
import '../../../core/widgets/layout.dart';
import '../data/page_models.dart';

/// Gradient header (eyebrow, title, intro) for an editable page.
class PageHeader extends StatelessWidget {
  const PageHeader({super.key, required this.page, this.actions = const []});

  final PageContent page;
  final List<Widget> actions;

  @override
  Widget build(BuildContext context) =>
      GradientHeader(eyebrow: page.eyebrow, title: page.title, intro: page.intro, actions: actions);
}

/// Image, body, steps and gallery of an editable page. The title and intro
/// are shown by [PageHeader] unless [showHeader] is true.
class PageContentView extends StatelessWidget {
  const PageContentView({
    super.key,
    required this.page,
    this.showSteps = true,
    this.stepsHeading,
    this.showHeader = false,
  });

  final PageContent page;
  final bool showSteps;
  final bool showHeader;

  /// Heading above the steps (e.g. "What happens next" on Booking).
  final String? stepsHeading;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (showHeader) ...[
          SectionHeading(eyebrow: page.eyebrow, title: page.title, level: 1),
          if (page.intro != null) ...[const SizedBox(height: 12), Text(page.intro!, style: theme.textTheme.bodyLarge)],
          const SizedBox(height: 20),
        ],
        if (page.imageUrl != null) ...[
          DecoratedBox(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(AppTheme.radius),
              boxShadow: BrandTokens.of(context).cardShadow,
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(AppTheme.radius),
              child: AppNetworkImage(url: page.imageUrl!, semanticLabel: page.imageAlt, aspectRatio: 16 / 9),
            ),
          ),
          const SizedBox(height: 20),
        ],
        if (page.body != null) ...[
          SurfaceCard(child: Text(page.body!, style: theme.textTheme.bodyLarge)),
          const SizedBox(height: 8),
        ],
        if (showSteps && page.steps.isNotEmpty) ...[
          const SizedBox(height: 20),
          if (stepsHeading != null) ...[SectionHeading(title: stepsHeading!), const SizedBox(height: 12)],
          PageStepsList(steps: page.steps),
        ],
        if (page.gallery.isNotEmpty) ...[
          const SizedBox(height: 28),
          const SectionHeading(title: 'Gallery'),
          const SizedBox(height: 12),
          GalleryGrid(images: page.gallery),
        ],
      ],
    );
  }
}

class PageStepsList extends StatelessWidget {
  const PageStepsList({super.key, required this.steps});

  final List<PageStep> steps;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (final (i, step) in steps.indexed)
          Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: SurfaceCard(
              padding: EdgeInsets.zero,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (step.imageUrl != null) AppNetworkImage(url: step.imageUrl!, aspectRatio: 16 / 9),
                  Padding(
                    padding: const EdgeInsets.all(18),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        StepNumber(i + 1),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Semantics(
                                header: step.title != null,
                                child: Text(
                                  step.title ?? 'Step ${i + 1}',
                                  semanticsLabel: 'Step ${i + 1}${step.title != null ? ': ${step.title}' : ''}',
                                  style: theme.textTheme.titleMedium,
                                ),
                              ),
                              if (step.text.isNotEmpty) ...[
                                const SizedBox(height: 4),
                                Text(step.text, style: theme.textTheme.bodyMedium),
                              ],
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }
}

class GalleryGrid extends StatelessWidget {
  const GalleryGrid({super.key, required this.images});

  final List<GalleryImage> images;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return ResponsiveGrid(
      minItemWidth: 160,
      children: [
        for (final image in images)
          Semantics(
            button: true,
            label: '${image.caption ?? 'Gallery image'}. Opens full screen',
            excludeSemantics: true,
            child: InkWell(
              borderRadius: BorderRadius.circular(16),
              onTap: () => ImageViewerScreen.open(context, url: image.imageUrl, caption: image.caption),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(16),
                    child: AppNetworkImage(url: image.imageUrl, aspectRatio: 1),
                  ),
                  if (image.caption != null)
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 6),
                      child: Text(image.caption!, style: theme.textTheme.bodySmall),
                    ),
                ],
              ),
            ),
          ),
      ],
    );
  }
}
