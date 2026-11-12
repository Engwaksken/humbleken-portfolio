import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/routes.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/format.dart';
import '../../../core/widgets/app_network_image.dart';
import '../../../core/widgets/tappable_card.dart';
import '../data/project_models.dart';

class ProjectCard extends StatelessWidget {
  const ProjectCard({super.key, required this.project});

  final ProjectSummary project;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return TappableCard(
      onTap: () => context.push(Routes.project(project.slug)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (project.imageUrl != null)
            AppNetworkImage(url: project.imageUrl!, aspectRatio: 16 / 9)
          else
            // Branded placeholder so every project card has a visual header.
            ExcludeSemantics(
              child: Container(
                height: 96,
                decoration: BoxDecoration(gradient: BrandTokens.of(context).heroGradient),
                alignment: Alignment.center,
                child: Icon(Icons.work_outline_rounded, size: 36, color: Colors.white.withValues(alpha: 0.85)),
              ),
            ),
          Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(project.title, style: theme.textTheme.titleMedium?.copyWith(fontSize: 17)),
                if (project.publishedAt != null) ...[
                  const SizedBox(height: 2),
                  Text(formatDate(project.publishedAt), style: theme.textTheme.bodySmall),
                ],
                if (project.summary != null) ...[
                  const SizedBox(height: 8),
                  Text(project.summary!, maxLines: 3, overflow: TextOverflow.ellipsis),
                ],
                if (project.technologies.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 6,
                    runSpacing: 6,
                    children: [for (final tech in project.technologies.take(6)) InfoPill(label: tech)],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
