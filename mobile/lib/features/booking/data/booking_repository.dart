import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/utils/format.dart';
import '../../../core/utils/json.dart';

class BookingRepository {
  BookingRepository(this._api);
  final ApiClient _api;

  /// `POST /booking-requests`; returns the server's confirmation message.
  Future<String> submit({
    required String name,
    required String email,
    String? phone,
    int? serviceId,
    required DateTime preferredAt,
    String? message,
  }) async {
    final json = await _api.post(
      'booking-requests',
      data: {
        'name': name.trim(),
        'email': email.trim(),
        if (phone != null && phone.trim().isNotEmpty) 'phone': phone.trim(),
        'service_id': ?serviceId,
        'preferred_at': toIso8601WithOffset(preferredAt),
        if (message != null && message.trim().isNotEmpty) 'message': message.trim(),
      },
    );
    return asStringOrNull(json['message']) ?? 'Thank you. Your booking request has been sent.';
  }
}

final bookingRepositoryProvider = Provider<BookingRepository>((ref) => BookingRepository(ref.watch(apiClientProvider)));
