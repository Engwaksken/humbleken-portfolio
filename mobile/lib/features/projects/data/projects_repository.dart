import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/paginated.dart';
import '../../../core/api/paginated_notifier.dart';
import '../../../core/utils/json.dart';
import 'project_models.dart';

class ProjectsRepository {
  ProjectsRepository(this._api);
  final ApiClient _api;

  Future<Paginated<ProjectSummary>> list({int page = 1}) async {
    final json = await _api.get('projects', query: {'page': page});
    return Paginated.fromJson(json, ProjectSummary.fromJson);
  }

  Future<ProjectDetail> detail(String slug) async {
    final json = await _api.get('projects/${Uri.encodeComponent(slug)}');
    return ProjectDetail.fromJson(asMap(json['data']));
  }
}

final projectsRepositoryProvider = Provider<ProjectsRepository>(
  (ref) => ProjectsRepository(ref.watch(apiClientProvider)),
);

class ProjectsNotifier extends PaginatedNotifier<ProjectSummary> {
  @override
  Future<Paginated<ProjectSummary>> fetchPage(int page) => ref.read(projectsRepositoryProvider).list(page: page);
}

final projectsProvider = AsyncNotifierProvider<ProjectsNotifier, PaginatedState<ProjectSummary>>(ProjectsNotifier.new);

final projectDetailProvider = FutureProvider.autoDispose.family<ProjectDetail, String>(
  (ref, slug) => ref.watch(projectsRepositoryProvider).detail(slug),
);
