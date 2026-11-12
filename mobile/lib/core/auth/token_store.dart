import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Persists the Sanctum token. Abstract so tests can use an in-memory store.
abstract class TokenStore {
  Future<String?> read();
  Future<void> write(String token);
  Future<void> delete();
}

/// Keychain (iOS) / Keystore-backed (Android) storage. On web this falls back
/// to the browser storage implementation provided by flutter_secure_storage.
class SecureTokenStore implements TokenStore {
  SecureTokenStore([FlutterSecureStorage? storage]) : _storage = storage ?? const FlutterSecureStorage();

  static const _key = 'api_token';
  final FlutterSecureStorage _storage;

  @override
  Future<String?> read() async {
    try {
      return await _storage.read(key: _key);
    } catch (_) {
      // Corrupted or inaccessible storage: treat as signed out.
      return null;
    }
  }

  @override
  Future<void> write(String token) => _storage.write(key: _key, value: token);

  @override
  Future<void> delete() async {
    try {
      await _storage.delete(key: _key);
    } catch (_) {
      // Nothing more we can do; the in-memory token is cleared regardless.
    }
  }
}

class MemoryTokenStore implements TokenStore {
  MemoryTokenStore([this._token]);
  String? _token;

  @override
  Future<String?> read() async => _token;
  @override
  Future<void> write(String token) async => _token = token;
  @override
  Future<void> delete() async => _token = null;
}

final tokenStoreProvider = Provider<TokenStore>((ref) => SecureTokenStore());

/// Token read from storage before `runApp`; overridden in `main()`.
final initialTokenProvider = Provider<String?>((ref) => null);

/// The current bearer token, or null when signed out.
class AuthToken extends Notifier<String?> {
  @override
  String? build() => ref.watch(initialTokenProvider);

  Future<void> set(String token) async {
    await ref.read(tokenStoreProvider).write(token);
    state = token;
  }

  Future<void> clear() async {
    state = null;
    await ref.read(tokenStoreProvider).delete();
  }
}

final authTokenProvider = NotifierProvider<AuthToken, String?>(AuthToken.new);
