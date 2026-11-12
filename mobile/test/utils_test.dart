import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:humbleken_portfolio/core/api/api_exception.dart';
import 'package:humbleken_portfolio/core/config/app_config.dart';
import 'package:humbleken_portfolio/core/theme/app_theme.dart';
import 'package:humbleken_portfolio/core/utils/format.dart';
import 'package:humbleken_portfolio/core/utils/links.dart';
import 'package:humbleken_portfolio/features/home/presentation/why_icons.dart';

void main() {
  group('Font Awesome icon mapping', () {
    const apiIcons = [
      'fa-comments',
      'fa-scale-balanced',
      'fa-circle-check',
      'fa-shield-halved',
      'fa-bolt',
      'fa-gears',
      'fa-server',
      'fa-network-wired',
      'fa-laptop-code',
      'fa-cloud',
      'fa-lock',
      'fa-headset',
      'fa-chart-line',
      'fa-lightbulb',
      'fa-clock',
      'fa-users',
    ];

    test('maps all 16 names from API.md to a specific icon', () {
      expect(whyIcons.keys.toSet(), apiIcons.toSet());
      for (final name in apiIcons) {
        expect(iconForFontAwesome(name), isNot(fallbackWhyIcon), reason: name);
      }
    });

    test('accepts prefixed class strings and bare names', () {
      expect(iconForFontAwesome('fa-solid fa-bolt'), Icons.bolt);
      expect(iconForFontAwesome('bolt'), Icons.bolt);
      expect(iconForFontAwesome(' FA-CLOUD '), Icons.cloud_outlined);
    });

    test('falls back for unknown or missing names', () {
      expect(iconForFontAwesome('fa-dragon'), fallbackWhyIcon);
      expect(iconForFontAwesome(''), fallbackWhyIcon);
      expect(iconForFontAwesome(null), fallbackWhyIcon);
    });
  });

  group('links', () {
    test('wa.me uses digits only', () {
      expect(whatsappUri('+254 (700) 000-111').toString(), 'https://wa.me/254700000111');
    });

    test('tel keeps a leading plus', () {
      expect(telUri('+254 700 000 111').toString(), 'tel:+254700000111');
    });

    test('embed URLs become watch URLs', () {
      expect(watchUrlFromEmbed('https://www.youtube.com/embed/abc123'), 'https://www.youtube.com/watch?v=abc123');
      expect(watchUrlFromEmbed('https://www.youtube-nocookie.com/embed/xyz'), 'https://www.youtube.com/watch?v=xyz');
      expect(watchUrlFromEmbed('https://player.vimeo.com/video/42'), 'https://vimeo.com/42');
      expect(watchUrlFromEmbed('https://example.test/video.mp4'), 'https://example.test/video.mp4');
    });
  });

  group('config', () {
    test('Android emulator uses the host loopback alias', () {
      expect(AppConfig.defaultBaseUrl(isWeb: false, platform: TargetPlatform.android), 'http://10.0.2.2:8000');
      expect(AppConfig.defaultBaseUrl(isWeb: true, platform: TargetPlatform.android), 'http://127.0.0.1:8000');
      expect(AppConfig.defaultBaseUrl(isWeb: false, platform: TargetPlatform.iOS), 'http://127.0.0.1:8000');
    });

    test('API v1 URL is derived from the origin', () {
      expect(const AppConfig(apiBaseUrl: 'https://example.test/').apiV1Url, 'https://example.test/api/v1');
    });
  });

  group('theme', () {
    test('parses hex colours', () {
      expect(parseHexColor('#077784'), const Color(0xFF077784));
      expect(parseHexColor('14263d'), const Color(0xFF14263D));
      expect(parseHexColor('#fff'), const Color(0xFFFFFFFF));
      expect(parseHexColor('nope'), isNull);
    });

    test('brand primary keeps AA contrast with its on-colour', () {
      final scheme = AppTheme.light(primaryHex: '#077784', secondaryHex: '#14263d').colorScheme;
      expect(scheme.primary, const Color(0xFF077784));
      expect(contrastRatio(scheme.primary, scheme.onPrimary), greaterThanOrEqualTo(4.5));
    });

    test('a low-contrast brand colour is replaced by the seeded tone', () {
      final scheme = AppTheme.light(primaryHex: '#ffee00').colorScheme;
      expect(scheme.primary, isNot(const Color(0xFFFFEE00)));
      expect(contrastRatio(scheme.primary, scheme.surface), greaterThanOrEqualTo(3));
    });
  });

  test('ISO 8601 with offset round-trips to the same instant', () {
    final picked = DateTime(2026, 12, 1, 14, 30);
    final encoded = toIso8601WithOffset(picked);
    expect(encoded, matches(RegExp(r'^2026-12-01T14:30:00[+-]\d{2}:\d{2}$')));
    expect(DateTime.parse(encoded).toUtc(), picked.toUtc());
  });

  test('422 responses expose field errors', () {
    final error = ApiException.fromDio(
      DioException(
        requestOptions: RequestOptions(path: 'enquiries'),
        response: Response(
          requestOptions: RequestOptions(path: 'enquiries'),
          statusCode: 422,
          data: {
            'message': 'The message field must be at least 10 characters.',
            'errors': {
              'message': ['The message field must be at least 10 characters.'],
            },
          },
        ),
      ),
    );
    expect(error.isValidation, isTrue);
    expect(error.fieldError('message'), 'The message field must be at least 10 characters.');
    expect(error.fieldError('email'), isNull);
  });
}
