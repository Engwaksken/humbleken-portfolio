import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/utils/json.dart';
import 'site_models.dart';

class SiteRepository {
  SiteRepository(this._api);
  final ApiClient _api;

  Future<SiteConfig> fetch() async {
    final json = await _api.get('site');
    return SiteConfig.fromJson(asMap(json['data']));
  }
}

final siteRepositoryProvider = Provider<SiteRepository>((ref) => SiteRepository(ref.watch(apiClientProvider)));

/// Site-wide configuration. Errors propagate so the shell can decide between
/// the fallback config and a retry.
final siteProvider = FutureProvider<SiteConfig>((ref) => ref.watch(siteRepositoryProvider).fetch());

/// Always-available config: the loaded site, or built-in defaults while
/// loading or after a failure.
final siteConfigProvider = Provider<SiteConfig>((ref) {
  return ref.watch(siteProvider).value ?? SiteConfig.fallback;
});
