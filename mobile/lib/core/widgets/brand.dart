import 'dart:ui' as ui;

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../config/app_config.dart';
import '../router/routes.dart';
import '../theme/app_theme.dart';
import 'app_drawer.dart';

/// The logo uploaded in the website admin, with any blank margin around the
/// artwork trimmed so it fills its space. Decorative unless [semanticLabel]
/// is given. While it loads, or when it is missing or fails (offline, or an
/// SVG logo), the bundled copy from [bundledAsset] is shown, unless a
/// [fallback] widget is given.
class BrandLogo extends StatefulWidget {
  const BrandLogo({super.key, required this.url, this.size = 32, this.fallback, this.semanticLabel});

  /// Copy of the uploaded logo made by tool/sync_brand.dart.
  static const bundledAsset = 'assets/brand/logo.png';

  final String? url;

  /// Height of the logo.
  final double size;
  final Widget? fallback;
  final String? semanticLabel;

  @override
  State<BrandLogo> createState() => _BrandLogoState();
}

class _BrandLogoState extends State<BrandLogo> {
  /// Trimmed logos by URL, so each one is only scanned once per run.
  static final _cache = <String, _Trimmed>{};

  _Trimmed? _logo;
  ImageStream? _stream;
  ImageStreamListener? _listener;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void didUpdateWidget(BrandLogo oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.url != widget.url) {
      _stopListening();
      _logo = null;
      _load();
    }
  }

  void _load() {
    final url = widget.url;
    if (url == null) return;
    final cached = _cache[url];
    if (cached != null) {
      _logo = cached;
      return;
    }
    // Decoded at a modest width: plenty for a header logo and quick to scan.
    final stream = CachedNetworkImageProvider(url, maxWidth: 900).resolve(ImageConfiguration.empty);
    final listener = ImageStreamListener(
      (info, _) async {
        final trimmed = await _Trimmed.from(info.image.clone());
        info.dispose();
        _cache[url] = trimmed;
        if (mounted && widget.url == url) setState(() => _logo = trimmed);
      },
      onError: (_, _) {}, // Keep showing the bundled logo.
    );
    stream.addListener(listener);
    _stream = stream;
    _listener = listener;
  }

  void _stopListening() {
    final listener = _listener;
    if (listener != null) _stream?.removeListener(listener);
    _stream = null;
    _listener = null;
  }

  @override
  void dispose() {
    _stopListening();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = widget.size;
    final logo = _logo;
    final Widget image = logo == null
        ? widget.fallback ?? Image.asset(BrandLogo.bundledAsset, height: size, fit: BoxFit.contain)
        : SizedBox(
            height: size,
            width: size * logo.crop.width / logo.crop.height,
            child: CustomPaint(painter: _CropPainter(logo)),
          );
    if (widget.semanticLabel == null) return ExcludeSemantics(child: image);
    return Semantics(
      label: widget.semanticLabel,
      image: true,
      child: ExcludeSemantics(child: image),
    );
  }
}

/// An image plus the rectangle that holds its artwork.
class _Trimmed {
  const _Trimmed(this.image, this.crop);

  final ui.Image image;
  final Rect crop;

  /// Finds the box around pixels that are neither transparent nor near-white.
  static Future<_Trimmed> from(ui.Image image) async {
    final full = Rect.fromLTWH(0, 0, image.width.toDouble(), image.height.toDouble());
    final data = await image.toByteData(format: ui.ImageByteFormat.rawStraightRgba);
    if (data == null) return _Trimmed(image, full);
    final w = image.width, h = image.height;
    var left = w, top = h, right = -1, bottom = -1;
    for (var y = 0; y < h; y++) {
      for (var x = 0; x < w; x++) {
        final i = (y * w + x) * 4;
        final ink =
            data.getUint8(i + 3) > 24 &&
            (data.getUint8(i) < 236 || data.getUint8(i + 1) < 236 || data.getUint8(i + 2) < 236);
        if (!ink) continue;
        if (x < left) left = x;
        if (x > right) right = x;
        if (y < top) top = y;
        if (y > bottom) bottom = y;
      }
    }
    if (right < 0) return _Trimmed(image, full);
    // A little breathing room, kept inside the image.
    final pad = 0.02 * (right - left);
    return _Trimmed(image, Rect.fromLTRB(left - pad, top - pad, right + 1 + pad, bottom + 1 + pad).intersect(full));
  }
}

class _CropPainter extends CustomPainter {
  const _CropPainter(this.logo);
  final _Trimmed logo;

  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawImageRect(logo.image, logo.crop, Offset.zero & size, Paint()..filterQuality = FilterQuality.high);
  }

  @override
  bool shouldRepaint(_CropPainter old) => old.logo != logo;
}

/// Name set as a two-tone wordmark ("Humble" + accent "Ken").
class BrandWordmark extends StatelessWidget {
  const BrandWordmark({super.key, required this.name, this.style, this.onDark = false});

  final String name;
  final TextStyle? style;

  /// White text with a lightened accent, for dark backgrounds.
  final bool onDark;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final base = (style ?? theme.textTheme.titleLarge)?.copyWith(
      fontWeight: FontWeight.w800,
      letterSpacing: -0.3,
      color: onDark ? Colors.white : null,
    );
    final accent = onDark ? Color.lerp(theme.colorScheme.primary, Colors.white, 0.6)! : theme.colorScheme.primary;
    final (lead, rest) = _split(name.trim());
    return Text.rich(
      TextSpan(
        children: [
          TextSpan(text: lead),
          if (rest.isNotEmpty)
            TextSpan(
              text: rest,
              style: TextStyle(color: accent),
            ),
        ],
      ),
      style: base,
      maxLines: 1,
      overflow: TextOverflow.ellipsis,
    );
  }

  /// "Tech Solutions" -> ("Tech", " Solutions"); "HumbleKen" -> ("Humble", "Ken").
  static (String, String) _split(String name) {
    final space = name.indexOf(' ');
    if (space > 0) return (name.substring(0, space), name.substring(space));
    final camel = RegExp(r'(?<=[a-z])(?=[A-Z])').firstMatch(name);
    if (camel != null) return (name.substring(0, camel.start), name.substring(camel.start));
    return (name, '');
  }
}

/// App bar for top-level screens: the app wordmark, a quick "Book"
/// action and the drawer button.
class BrandAppBar extends ConsumerWidget implements PreferredSizeWidget {
  const BrandAppBar({super.key, this.actions, this.bottom, this.onBrand = false});

  final List<Widget>? actions;
  final PreferredSizeWidget? bottom;

  /// True when the bar sits on the gradient header (home screen).
  final bool onBrand;

  @override
  Size get preferredSize => Size.fromHeight(64 + (bottom?.preferredSize.height ?? 0));

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final scheme = Theme.of(context).colorScheme;

    return AppBar(
      toolbarHeight: 64,
      backgroundColor: onBrand ? BrandTokens.of(context).deep : null,
      foregroundColor: onBrand ? Colors.white : null,
      titleSpacing: 4,
      title: Semantics(
        header: true,
        child: BrandWordmark(name: AppConfig.appName, onDark: onBrand),
      ),
      actions: [
        ...?actions,
        Padding(
          padding: const EdgeInsets.only(right: 12),
          child: IconButton.filledTonal(
            tooltip: 'Book a consultation',
            onPressed: () => context.push(Routes.booking),
            style: onBrand
                ? IconButton.styleFrom(
                    backgroundColor: Colors.white.withValues(alpha: 0.14),
                    foregroundColor: Colors.white,
                  )
                : IconButton.styleFrom(
                    backgroundColor: scheme.primary.withValues(alpha: 0.1),
                    foregroundColor: scheme.primary,
                  ),
            icon: const Icon(Icons.event_available_rounded),
          ),
        ),
      ],
      bottom: bottom,
    );
  }
}

/// Scaffold used by every top-level destination.
class TopLevelScaffold extends StatelessWidget {
  const TopLevelScaffold({
    super.key,
    required this.body,
    this.floatingActionButton,
    this.actions,
    this.onBrand = false,
    this.resizeToAvoidBottomInset,
  });

  final Widget body;
  final Widget? floatingActionButton;
  final List<Widget>? actions;
  final bool onBrand;
  final bool? resizeToAvoidBottomInset;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: resizeToAvoidBottomInset,
      appBar: BrandAppBar(actions: actions, onBrand: onBrand),
      drawer: const AppDrawer(),
      floatingActionButton: floatingActionButton,
      body: body,
    );
  }
}
