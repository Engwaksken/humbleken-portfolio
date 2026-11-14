import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_exception.dart';
import '../../../core/auth/token_store.dart';
import '../../../core/config/app_config.dart';
import '../../../core/utils/json.dart';

class AppUser {
  const AppUser({
    required this.id,
    required this.name,
    required this.email,
    required this.emailVerified,
    this.ticketReplyEmails = true,
  });

  final int id;
  final String name;
  final String email;
  final bool emailVerified;

  /// Whether the customer is emailed when I reply to one of their tickets.
  final bool ticketReplyEmails;

  factory AppUser.fromJson(Json json) => AppUser(
    id: asInt(json['id']),
    name: asString(json['name']),
    email: asString(json['email']),
    emailVerified: asBool(json['email_verified']),
    ticketReplyEmails: asBool(asMap(asMap(json['notification_preferences'])['ticket_reply'])['mail'], true),
  );
}

class AuthResult {
  const AuthResult({required this.token, required this.user});
  final String token;
  final AppUser user;

  factory AuthResult.fromJson(Json json) {
    final token = asStringOrNull(json['token']);
    if (token == null) throw const ApiException('The server did not return a sign-in token.');
    return AuthResult(token: token, user: AppUser.fromJson(asMap(json['user'])));
  }
}

class AuthRepository {
  AuthRepository(this._api);
  final ApiClient _api;

  Future<AuthResult> login({required String email, required String password}) async {
    final json = await _api.post(
      'auth/login',
      data: {'email': email.trim(), 'password': password, 'device_name': platformDeviceName()},
    );
    return AuthResult.fromJson(json);
  }

  Future<AuthResult> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final json = await _api.post(
      'auth/register',
      data: {
        'name': name.trim(),
        'email': email.trim(),
        'password': password,
        'password_confirmation': passwordConfirmation,
        'device_name': platformDeviceName(),
      },
    );
    return AuthResult.fromJson(json);
  }

  Future<String> forgotPassword(String email) async {
    final json = await _api.post('auth/forgot-password', data: {'email': email.trim()});
    return asStringOrNull(json['message']) ??
        'If an account exists for that email, a password reset link has been sent to it.';
  }

  Future<AppUser> currentUser() async {
    final json = await _api.get('auth/user');
    return AppUser.fromJson(asMap(json['data']));
  }

  Future<AppUser> updateTicketReplyEmails(bool enabled) async {
    final json = await _api.put(
      'auth/notification-preferences',
      data: {
        'notification_preferences': {
          'ticket_reply': {'mail': enabled},
        },
      },
    );
    return AppUser.fromJson(asMap(json['data']));
  }

  Future<String> resendVerification() async {
    final json = await _api.post('auth/email/verification-notification');
    return asStringOrNull(json['message']) ?? 'A new verification link has been sent to your email address.';
  }

  Future<void> logout() => _api.post('auth/logout');

  /// Permanently deletes the account and its data on the server.
  Future<void> deleteAccount(String password) => _api.delete('auth/account', data: {'password': password});
}

final authRepositoryProvider = Provider<AuthRepository>((ref) => AuthRepository(ref.watch(apiClientProvider)));

/// The signed-in user, or null when signed out. Rebuilds whenever the token
/// changes, so clearing the token (sign-out or a 401) signs the user out.
class AuthController extends AsyncNotifier<AppUser?> {
  @override
  Future<AppUser?> build() async {
    final token = ref.watch(authTokenProvider);
    if (token == null) return null;
    return ref.read(authRepositoryProvider).currentUser();
  }

  Future<void> signIn({required String email, required String password}) async {
    final result = await ref.read(authRepositoryProvider).login(email: email, password: password);
    await ref.read(authTokenProvider.notifier).set(result.token);
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final result = await ref
        .read(authRepositoryProvider)
        .register(name: name, email: email, password: password, passwordConfirmation: passwordConfirmation);
    await ref.read(authTokenProvider.notifier).set(result.token);
  }

  /// Re-fetches `/auth/user`, e.g. after verifying the email address.
  Future<void> refreshUser() async {
    ref.invalidateSelf();
    await future;
  }

  /// Saves the ticket reply email choice and updates the signed-in user.
  Future<void> setTicketReplyEmails(bool enabled) async {
    final user = await ref.read(authRepositoryProvider).updateTicketReplyEmails(enabled);
    state = AsyncData(user);
  }

  /// Deletes the account on the server, then forgets the (now revoked) token.
  Future<void> deleteAccount(String password) async {
    await ref.read(authRepositoryProvider).deleteAccount(password);
    await ref.read(authTokenProvider.notifier).clear();
  }

  Future<void> signOut() async {
    try {
      await ref.read(authRepositoryProvider).logout();
    } catch (_) {
      // Revoking server-side is best effort; the local token goes regardless.
    }
    await ref.read(authTokenProvider.notifier).clear();
  }
}

final authControllerProvider = AsyncNotifierProvider<AuthController, AppUser?>(AuthController.new);
