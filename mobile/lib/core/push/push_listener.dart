import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../router/app_router.dart';
import 'push_messaging.dart';
import 'push_registrar.dart';

/// Root messenger, so a notification that arrives while the app is open can be
/// shown on whichever screen is visible.
final rootMessengerKey = GlobalKey<ScaffoldMessengerState>();

/// Opens the right screen when a notification is tapped, and shows messages
/// that arrive while the app is open.
class PushListener extends ConsumerStatefulWidget {
  const PushListener({super.key, required this.child});

  final Widget child;

  @override
  ConsumerState<PushListener> createState() => _PushListenerState();
}

class _PushListenerState extends ConsumerState<PushListener> {
  final _subscriptions = <StreamSubscription<PushMessage>>[];

  @override
  void initState() {
    super.initState();
    final push = ref.read(pushMessagingProvider);
    _subscriptions
      ..add(push.onOpened.listen(_open))
      ..add(push.onForegroundMessage.listen(_showInApp));
    unawaited(
      push.initialMessage().then((message) {
        if (message != null) _open(message);
      }),
    );
  }

  @override
  void dispose() {
    for (final subscription in _subscriptions) {
      unawaited(subscription.cancel());
    }
    super.dispose();
  }

  void _open(PushMessage message) {
    final route = message.route;
    // Only in-app paths, never a URL from outside.
    if (!mounted || route == null || !route.startsWith('/') || route.startsWith('//')) return;
    ref.read(routerProvider).go(route);
  }

  void _showInApp(PushMessage message) {
    final text = [message.title, message.body].whereType<String>().join(': ');
    if (text.isEmpty) return;
    rootMessengerKey.currentState?.showSnackBar(
      SnackBar(
        content: Text(text),
        duration: const Duration(seconds: 6),
        action: message.route == null ? null : SnackBarAction(label: 'Open', onPressed: () => _open(message)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    // Keeps device registration running for the life of the app.
    ref.watch(pushRegistrarProvider);
    return widget.child;
  }
}
