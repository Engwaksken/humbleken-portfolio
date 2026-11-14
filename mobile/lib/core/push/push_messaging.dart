// firebase_messaging 17 deprecates getToken/onTokenRefresh/deleteToken in favour of
// register()/onRegistered, which return a Firebase Installation ID. The server sends
// through the FCM HTTP v1 API using registration tokens, so the token API is kept on
// purpose. Revisit when moving the server to installation-ID sending.
// ignore_for_file: deprecated_member_use

import 'dart:async';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// A push notification as the app needs it.
class PushMessage {
  const PushMessage({this.title, this.body, this.route});

  final String? title;
  final String? body;

  /// App route to open when tapped, e.g. `/account/tickets/12`.
  final String? route;
}

/// Push notifications, behind an interface so tests and unsupported platforms
/// use [NoPushMessaging].
abstract class PushMessaging {
  /// Asks for permission (Android 13+) and returns the device token, or null.
  Future<String?> requestToken();

  Stream<String> get onTokenRefresh;

  /// Messages that arrive while the app is open (Android does not show these).
  Stream<PushMessage> get onForegroundMessage;

  /// Notifications tapped while the app was in the background.
  Stream<PushMessage> get onOpened;

  /// The notification that launched the app from a closed state, if any.
  Future<PushMessage?> initialMessage();

  Future<void> deleteToken();
}

class NoPushMessaging implements PushMessaging {
  const NoPushMessaging();

  @override
  Future<String?> requestToken() async => null;

  @override
  Stream<String> get onTokenRefresh => const Stream.empty();

  @override
  Stream<PushMessage> get onForegroundMessage => const Stream.empty();

  @override
  Stream<PushMessage> get onOpened => const Stream.empty();

  @override
  Future<PushMessage?> initialMessage() async => null;

  @override
  Future<void> deleteToken() async {}
}

class FirebasePushMessaging implements PushMessaging {
  FirebasePushMessaging([FirebaseMessaging? messaging]) : _messaging = messaging ?? FirebaseMessaging.instance;

  final FirebaseMessaging _messaging;

  /// Starts Firebase on Android, where `google-services.json` configures it.
  /// Returns null (push off) on other platforms or if Firebase cannot start.
  static Future<PushMessaging?> initialize() async {
    if (kIsWeb || defaultTargetPlatform != TargetPlatform.android) return null;
    try {
      await Firebase.initializeApp();
      return FirebasePushMessaging();
    } catch (error) {
      debugPrint('Push notifications are unavailable: $error');
      return null;
    }
  }

  static PushMessage _convert(RemoteMessage message) => PushMessage(
    title: message.notification?.title,
    body: message.notification?.body,
    route: message.data['route'] as String?,
  );

  @override
  Future<String?> requestToken() async {
    try {
      final settings = await _messaging.requestPermission();
      if (settings.authorizationStatus == AuthorizationStatus.denied) return null;
      return await _messaging.getToken();
    } catch (error) {
      debugPrint('Could not get a push token: $error');
      return null;
    }
  }

  @override
  Stream<String> get onTokenRefresh => _messaging.onTokenRefresh;

  @override
  Stream<PushMessage> get onForegroundMessage => FirebaseMessaging.onMessage.map(_convert);

  @override
  Stream<PushMessage> get onOpened => FirebaseMessaging.onMessageOpenedApp.map(_convert);

  @override
  Future<PushMessage?> initialMessage() async {
    final message = await _messaging.getInitialMessage();
    return message == null ? null : _convert(message);
  }

  @override
  Future<void> deleteToken() async {
    try {
      await _messaging.deleteToken();
    } catch (_) {
      // Best effort; the server forgets the token on sign-out anyway.
    }
  }
}

/// Overridden in `main.dart` with Firebase on Android.
final pushMessagingProvider = Provider<PushMessaging>((ref) => const NoPushMessaging());
