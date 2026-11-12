import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/utils/json.dart';

class EnquiryRepository {
  EnquiryRepository(this._api);
  final ApiClient _api;

  /// `POST /enquiries`; returns the server's confirmation message.
  Future<String> submit({
    required String name,
    required String email,
    String? phone,
    int? serviceId,
    required String message,
  }) async {
    final json = await _api.post(
      'enquiries',
      data: {
        'name': name.trim(),
        'email': email.trim(),
        if (phone != null && phone.trim().isNotEmpty) 'phone': phone.trim(),
        'service_id': ?serviceId,
        'message': message.trim(),
      },
    );
    return asStringOrNull(json['message']) ?? 'Thank you. Your enquiry has been sent.';
  }
}

final enquiryRepositoryProvider = Provider<EnquiryRepository>((ref) => EnquiryRepository(ref.watch(apiClientProvider)));
