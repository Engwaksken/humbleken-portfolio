import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app.dart';
import 'core/auth/token_store.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Read the saved token before the first frame so a signed-in user does not
  // briefly see the sign-in form.
  final tokenStore = SecureTokenStore();
  final token = await tokenStore.read();

  runApp(
    ProviderScope(
      // Screens show explicit "Try again" buttons, so disable Riverpod's
      // automatic retry of failed providers.
      retry: (_, _) => null,
      overrides: [tokenStoreProvider.overrideWithValue(tokenStore), initialTokenProvider.overrideWithValue(token)],
      child: const PortfolioApp(),
    ),
  );
}
