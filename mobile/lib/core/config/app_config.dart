import 'package:flutter/foundation.dart';

/// Runtime configuration resolved from `--dart-define` values.
class AppConfig {
  /// Name shown for the app: launcher label, header and menu.
  static const String appName = 'HumbleKen';

  const AppConfig({required this.apiBaseUrl});

  /// Origin of the Laravel app, without the `/api/v1` suffix,
  /// e.g. `https://www.humbleken.com`.
  final String apiBaseUrl;

  /// Base URL every API request is resolved against.
  String get apiV1Url => '${_trimSlash(apiBaseUrl)}/api/v1';

  /// The live website. Every build talks to it unless told otherwise.
  static const String productionUrl = 'https://www.humbleken.com';

  /// `--dart-define=API_BASE_URL=https://staging.example.com` points the app at any server.
  static const String _definedUrl = String.fromEnvironment('API_BASE_URL');

  /// `--dart-define=LOCAL_API=true` points the app at a local `php artisan serve`.
  static const bool _useLocal = bool.fromEnvironment('LOCAL_API');

  /// Resolution order:
  /// 1. `API_BASE_URL`, when set.
  /// 2. A local server, when `LOCAL_API=true` (see [localBaseUrl]).
  /// 3. The live site, for debug, profile and release builds alike.
  factory AppConfig.fromEnvironment() {
    return AppConfig(
      apiBaseUrl: resolveBaseUrl(
        definedUrl: _definedUrl,
        useLocal: _useLocal,
        isWeb: kIsWeb,
        platform: defaultTargetPlatform,
      ),
    );
  }

  static String resolveBaseUrl({
    String definedUrl = '',
    bool useLocal = false,
    required bool isWeb,
    required TargetPlatform platform,
  }) {
    final trimmed = definedUrl.trim();
    if (trimmed.isNotEmpty) return trimmed;
    if (useLocal) return localBaseUrl(isWeb: isWeb, platform: platform);
    return productionUrl;
  }

  /// Local `php artisan serve`: `10.0.2.2:8000` on the Android emulator (its alias
  /// for the host machine's loopback) and `127.0.0.1:8000` everywhere else.
  static String localBaseUrl({required bool isWeb, required TargetPlatform platform}) {
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
