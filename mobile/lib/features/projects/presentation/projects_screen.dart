import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/widgets/brand.dart';
import '../../../core/widgets/layout.dart';
import '../../../core/widgets/paginated_list_view.dart';
import '../data/projects_repository.dart';
import 'project_card.dart';

class ProjectsScreen extends ConsumerWidget {
  const ProjectsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notifier = ref.read(projectsProvider.notifier);
    return TopLevelScaffold(
      body: PaginatedListView(
        value: ref.watch(projectsProvider),
        header: const SectionHeading(eyebrow: 'Portfolio', title: 'Work', level: 1),
        emptyMessage: 'No projects have been published yet.',
        itemBuilder: (context, project) => ContentWidth(child: ProjectCard(project: project)),
        onLoadMore: notifier.loadMore,
        onRetryLoadMore: notifier.retryLoadMore,
        onRefresh: () async {
          try {
            ref.invalidate(projectsProvider);
            await ref.read(projectsProvider.future);
          } catch (_) {}
        },
      ),
    );
  }
}
