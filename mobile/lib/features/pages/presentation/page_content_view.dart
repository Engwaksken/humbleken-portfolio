import 'package:flutter/material.dart';

import '../../../core/widgets/app_network_image.dart';
import '../../../core/widgets/image_viewer.dart';
import '../../../core/widgets/layout.dart';
import '../data/page_models.dart';

/// Header, body, steps and gallery of an editable page.
class PageContentView extends StatelessWidget {
  const PageContentView({super.key, required this.page, this.showSteps = true, this.stepsHeading});

  final PageContent page;
  final bool showSteps;

  /// Heading above the steps (e.g. "What happens next" on Booking).
  final String? stepsHeading;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        SectionHeading(eyebrow: page.eyebrow, title: page.title, level: 1),
        if (page.intro != null) ...[const SizedBox(height: 12), Text(page.intro!, style: theme.textTheme.bodyLarge)],
        if (page.imageUrl != null) ...[
          const SizedBox(height: 20),
          ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: AppNetworkImage(url: page.imageUrl!, semanticLabel: page.imageAlt, aspectRatio: 16 / 9),
          ),
        ],
        if (page.body != null) ...[const SizedBox(height: 16), Text(page.body!, style: theme.textTheme.bodyLarge)],
        if (showSteps && page.steps.isNotEmpty) ...[
          const SizedBox(height: 28),
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
            child: Card(
              margin: EdgeInsets.zero,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (step.imageUrl != null) AppNetworkImage(url: step.imageUrl!, aspectRatio: 16 / 9),
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        StepNumber(i + 1),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Semantics(
                                header: step.title != null,
                                child: Text(
                                  step.title ?? 'Step ${i + 1}',
                                  semanticsLabel: 'Step ${i + 1}${step.title != null ? ': ${step.title}' : ''}',
                                  style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
                                ),
                              ),
                              if (step.text.isNotEmpty) ...[const SizedBox(height: 4), Text(step.text)],
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
              borderRadius: BorderRadius.circular(12),
              onTap: () => ImageViewerScreen.open(context, url: image.imageUrl, caption: image.caption),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
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
