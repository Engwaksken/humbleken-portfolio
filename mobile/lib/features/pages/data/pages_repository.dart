import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/utils/json.dart';
import '../../home/data/home_models.dart';
import 'page_models.dart';

class PagesRepository {
  PagesRepository(this._api);
  final ApiClient _api;

  Future<HomePage> home() async {
    final json = await _api.get('pages/home');
    return HomePage.fromJson(asMap(json['data']));
  }

  /// [key] is one of `services`, `approach`, `booking`, `contact`.
  Future<PageContent> page(String key) async {
    final json = await _api.get('pages/${Uri.encodeComponent(key)}');
    return PageContent.fromJson(asMap(json['data']));
  }
}

final pagesRepositoryProvider = Provider<PagesRepository>((ref) => PagesRepository(ref.watch(apiClientProvider)));

final homePageProvider = FutureProvider<HomePage>((ref) => ref.watch(pagesRepositoryProvider).home());

final pageContentProvider = FutureProvider.family<PageContent, String>(
  (ref, key) => ref.watch(pagesRepositoryProvider).page(key),
);
