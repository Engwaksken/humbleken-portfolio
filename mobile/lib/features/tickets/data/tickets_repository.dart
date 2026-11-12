import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/paginated.dart';
import '../../../core/api/paginated_notifier.dart';
import '../../../core/auth/token_store.dart';
import '../../../core/utils/json.dart';
import 'ticket_models.dart';

class TicketsRepository {
  TicketsRepository(this._api);
  final ApiClient _api;

  Future<Paginated<TicketSummary>> list({int page = 1}) async {
    final json = await _api.get('tickets', query: {'page': page});
    return Paginated.fromJson(json, TicketSummary.fromJson);
  }

  Future<TicketDetail> detail(int id) async {
    final json = await _api.get('tickets/$id');
    return TicketDetail.fromJson(asMap(json['data']));
  }

  /// Text-only ticket (JSON body; attachments are not supported in the app).
  Future<TicketDetail> create({required String subject, required String body}) async {
    final json = await _api.post('tickets', data: {'subject': subject.trim(), 'body': body.trim()});
    return TicketDetail.fromJson(asMap(json['data']));
  }

  Future<TicketDetail> reply(int id, String body) async {
    final json = await _api.post('tickets/$id/replies', data: {'body': body.trim()});
    return TicketDetail.fromJson(asMap(json['data']));
  }
}

final ticketsRepositoryProvider = Provider<TicketsRepository>((ref) => TicketsRepository(ref.watch(apiClientProvider)));

class TicketsNotifier extends PaginatedNotifier<TicketSummary> {
  @override
  Future<PaginatedState<TicketSummary>> build() {
    // Reset when the signed-in account changes.
    ref.watch(authTokenProvider);
    return super.build();
  }

  @override
  Future<Paginated<TicketSummary>> fetchPage(int page) => ref.read(ticketsRepositoryProvider).list(page: page);
}

final ticketsProvider = AsyncNotifierProvider.autoDispose<TicketsNotifier, PaginatedState<TicketSummary>>(
  TicketsNotifier.new,
);

class TicketDetailNotifier extends AsyncNotifier<TicketDetail> {
  TicketDetailNotifier(this.id);
  final int id;

  @override
  Future<TicketDetail> build() {
    ref.watch(authTokenProvider);
    return ref.read(ticketsRepositoryProvider).detail(id);
  }

  /// Posts a reply and replaces the thread with the server's version.
  Future<void> reply(String body) async {
    final updated = await ref.read(ticketsRepositoryProvider).reply(id, body);
    if (!ref.mounted) return;
    state = AsyncData(updated);
    ref.invalidate(ticketsProvider);
  }
}

final ticketDetailProvider = AsyncNotifierProvider.autoDispose.family<TicketDetailNotifier, TicketDetail, int>(
  TicketDetailNotifier.new,
);
