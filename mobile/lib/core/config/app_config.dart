import 'package:flutter/foundation.dart';

/// App-wide configuration.
class AppConfig {
  /// Name shown for the app: launcher label, header and menu.
  static const String appName = 'HumbleKen';

  const AppConfig({this.apiBaseUrl = productionUrl});

  /// The live website. The app always talks to it.
  static const String productionUrl = 'https://www.humbleken.com';

  /// Origin of the Laravel app, without the `/api/v1` suffix.
  final String apiBaseUrl;

  /// Base URL every API request is resolved against.
  String get apiV1Url => '${_trimSlash(apiBaseUrl)}/api/v1';

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
