import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/utils/json.dart';
import 'service_models.dart';

class ServicesRepository {
  ServicesRepository(this._api);
  final ApiClient _api;

  Future<ServiceCatalogue> catalogue() async => ServiceCatalogue.fromJson(await _api.get('services'));

  Future<ServiceDetail> detail(String slug) async {
    final json = await _api.get('services/${Uri.encodeComponent(slug)}');
    return ServiceDetail.fromJson(asMap(json['data']));
  }
}

final servicesRepositoryProvider = Provider<ServicesRepository>(
  (ref) => ServicesRepository(ref.watch(apiClientProvider)),
);

/// Cached for the session: used by the Services tab and the form dropdowns.
final serviceCatalogueProvider = FutureProvider<ServiceCatalogue>(
  (ref) => ref.watch(servicesRepositoryProvider).catalogue(),
);

final serviceDetailProvider = FutureProvider.autoDispose.family<ServiceDetail, String>(
  (ref, slug) => ref.watch(servicesRepositoryProvider).detail(slug),
);
