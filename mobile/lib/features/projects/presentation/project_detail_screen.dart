import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/utils/format.dart';
import '../../../core/utils/links.dart';
import '../../../core/widgets/app_network_image.dart';
import '../../../core/widgets/layout.dart';
import '../../../core/widgets/markdown_view.dart';
import '../../../core/widgets/state_views.dart';
import '../../../core/widgets/tappable_card.dart';
import '../data/project_models.dart';
import '../data/projects_repository.dart';

class ProjectDetailScreen extends ConsumerWidget {
  const ProjectDetailScreen({super.key, required this.slug});

  final String slug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final project = ref.watch(projectDetailProvider(slug));
    return Scaffold(
      appBar: brandedAppBar(context),
      body: switch (project) {
        AsyncValue(:final value?) => _ProjectBody(project: value),
        AsyncValue(:final error?) => ErrorView(
          error: error,
          onRetry: () => ref.invalidate(projectDetailProvider(slug)),
        ),
        _ => const LoadingView(label: 'Loading project'),
      },
    );
  }
}

class _ProjectBody extends StatelessWidget {
  const _ProjectBody({required this.project});

  final ProjectDetail project;

  @override
  Widget build(BuildContext context) {
    return BrandedPageBody(
      header: GradientHeader(
        eyebrow: project.publishedAt != null ? 'Project · ${formatDate(project.publishedAt)}' : 'Project',
        title: project.title,
        intro: project.summary,
      ),
      children: [
        if (project.technologies.isNotEmpty) ...[
          Semantics(
            label: 'Technologies: ${project.technologies.join(', ')}',
            excludeSemantics: true,
            child: Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [for (final t in project.technologies) InfoPill(label: t)],
            ),
          ),
          const SizedBox(height: 16),
        ],
        if (project.imageUrl != null) ...[
          ClipRRect(
            borderRadius: BorderRadius.circular(20),
            child: AppNetworkImage(url: project.imageUrl!, aspectRatio: 16 / 9),
          ),
        ],
        if (project.videoEmbedUrl != null) ...[
          const SizedBox(height: 16),
          Align(
            alignment: Alignment.centerLeft,
            child: OutlinedButton.icon(
              onPressed: () => openExternalUrl(context, watchUrlFromEmbed(project.videoEmbedUrl!)),
              icon: const Icon(Icons.play_circle_outline),
              label: const Text('Watch the video'),
            ),
          ),
        ],
        if (project.bodyMarkdown != null) ...[const SizedBox(height: 16), MarkdownView(data: project.bodyMarkdown!)],
      ],
    );
  }
}
