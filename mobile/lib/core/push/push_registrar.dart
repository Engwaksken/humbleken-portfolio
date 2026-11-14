import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../api/api_client.dart';
import '../auth/token_store.dart';
import 'push_messaging.dart';

/// `POST/DELETE /devices`: tells the server which phones to notify.
class DevicesRepository {
  DevicesRepository(this._api);
  final ApiClient _api;

  Future<void> register(String token) => _api.post('devices', data: {'token': token, 'platform': 'android'});

  Future<void> unregister(String token) => _api.delete('devices', data: {'token': token});
}

final devicesRepositoryProvider = Provider<DevicesRepository>((ref) => DevicesRepository(ref.watch(apiClientProvider)));

/// Registers this phone with the server while a customer is signed in, so
/// they get push notifications for ticket replies and booking updates.
class PushRegistrar extends Notifier<String?> {
  StreamSubscription<String>? _refresh;

  @override
  String? build() {
    ref.onDispose(() => _refresh?.cancel());

    // Watches the API token rather than the signed-in user, so that sign-out
    // (in AuthController) can call unregister() without a provider cycle.
    ref.listen(authTokenProvider, (previous, next) {
      if (next != null && previous == null) unawaited(register());
    }, fireImmediately: true);

    return null;
  }

  PushMessaging get _push => ref.read(pushMessagingProvider);

  /// The registered token (state), or null when push is off.
  Future<void> register() async {
    final token = await _push.requestToken();
    if (token == null) return;
    await _send(token);

    await _refresh?.cancel();
    _refresh = _push.onTokenRefresh.listen((fresh) => unawaited(_send(fresh)));
  }

  Future<void> _send(String token) async {
    try {
      await ref.read(devicesRepositoryProvider).register(token);
      state = token;
    } catch (error) {
      debugPrint('Could not register this device for notifications: $error');
    }
  }

  /// Called before signing out, while the API token is still valid.
  Future<void> unregister() async {
    final token = state;
    await _refresh?.cancel();
    _refresh = null;
    state = null;
    if (token == null) return;
    try {
      await ref.read(devicesRepositoryProvider).unregister(token);
    } catch (_) {
      // The server also drops tokens Firebase reports as invalid.
    }
    await _push.deleteToken();
  }

  /// After account deletion the server has already removed the token.
  Future<void> forgetLocally() async {
    await _refresh?.cancel();
    _refresh = null;
    state = null;
    await _push.deleteToken();
  }
}

final pushRegistrarProvider = NotifierProvider<PushRegistrar, String?>(PushRegistrar.new);
