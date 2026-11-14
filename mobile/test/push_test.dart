import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:go_router/go_router.dart';
import 'package:humbleken_portfolio/core/auth/token_store.dart';
import 'package:humbleken_portfolio/core/push/push_listener.dart';
import 'package:humbleken_portfolio/core/push/push_messaging.dart';
import 'package:humbleken_portfolio/core/push/push_registrar.dart';
import 'package:humbleken_portfolio/core/router/app_router.dart';
import 'package:humbleken_portfolio/features/auth/data/auth_repository.dart';

class _FakePush implements PushMessaging {
  _FakePush({this.token = 'fcm-token'});

  final String? token;
  final opened = StreamController<PushMessage>.broadcast();
  final foreground = StreamController<PushMessage>.broadcast();
  bool deleted = false;

  @override
  Future<String?> requestToken() async => token;

  @override
  Stream<String> get onTokenRefresh => const Stream.empty();

  @override
  Stream<PushMessage> get onForegroundMessage => foreground.stream;

  @override
  Stream<PushMessage> get onOpened => opened.stream;

  @override
  Future<PushMessage?> initialMessage() async => null;

  @override
  Future<void> deleteToken() async => deleted = true;
}

class _FakeDevices implements DevicesRepository {
  final registered = <String>[];
  final unregistered = <String>[];

  @override
  Future<void> register(String token) async => registered.add(token);

  @override
  Future<void> unregister(String token) async => unregistered.add(token);
}

class _FakeAuth implements AuthRepository {
  @override
  Future<AppUser> currentUser() async =>
      const AppUser(id: 1, name: 'Ann', email: 'ann@example.test', emailVerified: true);

  @override
  Future<void> logout() async {}

  @override
  dynamic noSuchMethod(Invocation invocation) => super.noSuchMethod(invocation);
}

void main() {
  test('registers the phone after sign-in and removes it on sign-out', () async {
    final push = _FakePush();
    final devices = _FakeDevices();
    final container = ProviderContainer(
      overrides: [
        tokenStoreProvider.overrideWithValue(MemoryTokenStore()),
        initialTokenProvider.overrideWithValue('api-token'),
        authRepositoryProvider.overrideWithValue(_FakeAuth()),
        pushMessagingProvider.overrideWithValue(push),
        devicesRepositoryProvider.overrideWithValue(devices),
      ],
    );
    addTearDown(container.dispose);

    container.listen(pushRegistrarProvider, (_, _) {});
    await container.read(authControllerProvider.future);
    await pumpEventQueue();

    expect(devices.registered, ['fcm-token']);
    expect(container.read(pushRegistrarProvider), 'fcm-token');

    await container.read(authControllerProvider.notifier).signOut();

    expect(devices.unregistered, ['fcm-token']);
    expect(push.deleted, isTrue);
    expect(container.read(pushRegistrarProvider), isNull);
  });

  test('does nothing when notification permission is refused', () async {
    final devices = _FakeDevices();
    final container = ProviderContainer(
      overrides: [
        tokenStoreProvider.overrideWithValue(MemoryTokenStore()),
        initialTokenProvider.overrideWithValue('api-token'),
        authRepositoryProvider.overrideWithValue(_FakeAuth()),
        pushMessagingProvider.overrideWithValue(_FakePush(token: null)),
        devicesRepositoryProvider.overrideWithValue(devices),
      ],
    );
    addTearDown(container.dispose);

    container.listen(pushRegistrarProvider, (_, _) {});
    await container.read(authControllerProvider.future);
    await pumpEventQueue();

    expect(devices.registered, isEmpty);
  });

  testWidgets('tapping a notification opens its screen; outside links are ignored', (tester) async {
    final push = _FakePush();
    final router = GoRouter(
      initialLocation: '/home',
      routes: [
        GoRoute(
          path: '/home',
          builder: (_, _) => const Scaffold(body: Text('home screen')),
        ),
        GoRoute(
          path: '/account/tickets/:id',
          builder: (_, state) => Scaffold(body: Text('ticket ${state.pathParameters['id']}')),
        ),
      ],
    );

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStoreProvider.overrideWithValue(MemoryTokenStore()),
          pushMessagingProvider.overrideWithValue(push),
          devicesRepositoryProvider.overrideWithValue(_FakeDevices()),
          routerProvider.overrideWithValue(router),
        ],
        child: MaterialApp.router(
          scaffoldMessengerKey: rootMessengerKey,
          routerConfig: router,
          builder: (context, child) => PushListener(child: child!),
        ),
      ),
    );
    await tester.pumpAndSettle();

    push.opened.add(const PushMessage(route: '//evil.example/x'));
    await tester.pumpAndSettle();
    expect(find.text('home screen'), findsOneWidget);

    push.opened.add(const PushMessage(route: '/account/tickets/7'));
    await tester.pumpAndSettle();
    expect(find.text('ticket 7'), findsOneWidget);

    push.foreground.add(const PushMessage(title: 'New reply on ticket #8', body: 'Tap to read the reply.'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 500));
    expect(find.text('New reply on ticket #8: Tap to read the reply.'), findsOneWidget);
    // Let the snackbar time out so no timers are left running.
    await tester.pump(const Duration(seconds: 7));
    await tester.pumpAndSettle();
  });

  test('AppUser reads notification choices per event and channel', () {
    final user = AppUser.fromJson({
      'id': 1,
      'name': 'A',
      'email': 'a@b.c',
      'notification_preferences': {
        'ticket_reply': {'mail': true, 'app': false},
      },
    });
    expect(user.wantsNotification('ticket_reply', 'app'), isFalse);
    expect(user.wantsNotification('ticket_reply', 'mail'), isTrue);
    expect(user.wantsNotification('booking_update', 'app'), isTrue, reason: 'missing means on');
  });
}
