import 'package:flutter/foundation.dart';

/// Runtime configuration resolved from `--dart-define` values.
class AppConfig {
  const AppConfig({required this.apiBaseUrl});

  /// Origin of the Laravel app, without the `/api/v1` suffix,
  /// e.g. `http://127.0.0.1:8000`.
  final String apiBaseUrl;

  /// Base URL every API request is resolved against.
  String get apiV1Url => '${_trimSlash(apiBaseUrl)}/api/v1';

  static const String _defined = String.fromEnvironment('API_BASE_URL');

  /// Uses `--dart-define=API_BASE_URL=...` when provided. Otherwise defaults to
  /// `10.0.2.2:8000` on Android (the emulator's alias for the host machine's
  /// loopback) and `127.0.0.1:8000` everywhere else.
  factory AppConfig.fromEnvironment() {
    if (_defined.isNotEmpty) return const AppConfig(apiBaseUrl: _defined);
    return AppConfig(
      apiBaseUrl: defaultBaseUrl(isWeb: kIsWeb, platform: defaultTargetPlatform),
    );
  }

  static String defaultBaseUrl({required bool isWeb, required TargetPlatform platform}) {
    if (!isWeb && platform == TargetPlatform.android) return 'http://10.0.2.2:8000';
    return 'http://127.0.0.1:8000';
  }

  static String _trimSlash(String url) => url.endsWith('/') ? url.substring(0, url.length - 1) : url;
}

/// Human-readable platform name, sent as the Sanctum token `device_name`.
String platformDeviceName() {
  if (kIsWeb) return 'Web browser';
  return switch (defaultTargetPlatform) {
    TargetPlatform.android => 'Android',
    TargetPlatform.iOS => 'iOS',
    TargetPlatform.macOS => 'macOS',
    TargetPlatform.windows => 'Windows',
    TargetPlatform.linux => 'Linux',
    TargetPlatform.fuchsia => 'Fuchsia',
  };
}
