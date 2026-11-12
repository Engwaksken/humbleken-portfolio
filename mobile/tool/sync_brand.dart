// Downloads the logo and favicon uploaded in the website admin and turns them
// into the app's launcher icon, splash and bundled fallback logo.
//
// Usage (from mobile/):
//   dart run tool/sync_brand.dart [site origin, default https://www.humbleken.com]
//   dart run flutter_launcher_icons
//   dart run flutter_native_splash:create
import 'dart:convert';
import 'dart:io';
import 'dart:math' as math;
import 'dart:typed_data';

import 'package:image/image.dart' as img;

const _outDir = 'assets/brand';

Future<void> main(List<String> args) async {
  final origin = (args.isEmpty ? 'https://www.humbleken.com' : args.first).replaceAll(RegExp(r'/+$'), '');
  final client = HttpClient()..userAgent = 'humbleken-mobile-build';
  try {
    final site = jsonDecode(utf8.decode(await _get(client, Uri.parse('$origin/api/v1/site'))))['data'] as Map;
    final logoUrl = site['logo_url'] as String?;
    final faviconUrl = site['favicon_url'] as String?;
    if (logoUrl == null && faviconUrl == null) {
      stderr.writeln('The site has no logo or favicon uploaded; nothing to do.');
      exitCode = 1;
      return;
    }
    Directory(_outDir).createSync(recursive: true);

    final logo = logoUrl == null ? null : _decode(await _get(client, Uri.parse(logoUrl)), 'logo');
    final favicon = faviconUrl == null ? null : _decode(await _get(client, Uri.parse(faviconUrl)), 'favicon');

    // The icon prefers the favicon (a compact mark); the logo is the fallback.
    final mark = _trim((favicon ?? logo)!);
    _write('icon.png', _onCanvas(mark, size: 1024, fill: 0.7, background: img.ColorRgb8(255, 255, 255)));
    // Adaptive icon foreground: Android masks it to the middle ~66%.
    _write('icon_foreground.png', _onCanvas(mark, size: 1024, fill: 0.56));
    // Bundled chatbot avatar, shown when the live favicon is unavailable.
    _write('favicon.png', _onCanvas(mark, size: 256, fill: 1));
    // Android 12+ splash shows the icon inside a circle (~2/3 of 1152).
    _write('splash_icon.png', _onCanvas(mark, size: 1152, fill: 0.5));

    if (logo != null) {
      final trimmed = _trim(logo);
      final wide = img.copyResize(
        trimmed,
        width: math.min(trimmed.width, 1200),
        interpolation: img.Interpolation.cubic,
      );
      _write('logo.png', _pad(wide, 24));
    }
    stdout.writeln(
      'Brand assets written to $_outDir. Now run:\n'
      '  dart run flutter_launcher_icons\n'
      '  dart run flutter_native_splash:create',
    );
  } finally {
    client.close();
  }
}

Future<List<int>> _get(HttpClient client, Uri uri) async {
  final response = await (await client.getUrl(uri)).close();
  if (response.statusCode != 200) throw HttpException('GET $uri -> ${response.statusCode}', uri: uri);
  return response.fold<List<int>>(<int>[], (all, chunk) => all..addAll(chunk));
}

img.Image _decode(List<int> bytes, String what) {
  final image = img.decodeImage(Uint8List.fromList(bytes));
  if (image == null) throw FormatException('Could not decode the $what (SVG is not supported).');
  return image.convert(numChannels: 4);
}

/// Crops away transparent or near-white margins.
img.Image _trim(img.Image src) {
  bool ink(img.Pixel p) => p.a > 24 && (p.r < 236 || p.g < 236 || p.b < 236);
  var left = src.width, top = src.height, right = -1, bottom = -1;
  for (final p in src) {
    if (!ink(p)) continue;
    left = math.min(left, p.x);
    right = math.max(right, p.x);
    top = math.min(top, p.y);
    bottom = math.max(bottom, p.y);
  }
  if (right < 0) return src;
  return img.copyCrop(src, x: left, y: top, width: right - left + 1, height: bottom - top + 1);
}

/// Centres [src] on a square canvas, scaled to [fill] of its side.
img.Image _onCanvas(img.Image src, {required int size, required double fill, img.Color? background}) {
  final canvas = img.Image(width: size, height: size, numChannels: 4);
  img.fill(canvas, color: background ?? img.ColorRgba8(0, 0, 0, 0));
  final scale = size * fill / math.max(src.width, src.height);
  final scaled = img.copyResize(
    src,
    width: (src.width * scale).round(),
    height: (src.height * scale).round(),
    interpolation: img.Interpolation.cubic,
  );
  return img.compositeImage(canvas, scaled, dstX: (size - scaled.width) ~/ 2, dstY: (size - scaled.height) ~/ 2);
}

img.Image _pad(img.Image src, int padding) {
  final canvas = img.Image(width: src.width + padding * 2, height: src.height + padding * 2, numChannels: 4);
  img.fill(canvas, color: img.ColorRgba8(255, 255, 255, 0));
  return img.compositeImage(canvas, src, dstX: padding, dstY: padding);
}

void _write(String name, img.Image image) {
  File('$_outDir/$name').writeAsBytesSync(img.encodePng(image));
  stdout.writeln('  $_outDir/$name (${image.width}x${image.height})');
}
