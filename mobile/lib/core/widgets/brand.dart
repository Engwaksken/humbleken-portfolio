import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../config/app_config.dart';
import '../router/routes.dart';
import '../theme/app_theme.dart';
import 'app_drawer.dart';

/// Brand logo; decorative unless [semanticLabel] is given.
/// Renders [fallback] (or nothing) if the image fails, e.g. an SVG logo.
class BrandLogo extends StatelessWidget {
  const BrandLogo({super.key, required this.url, this.size = 32, this.fallback, this.semanticLabel});

  final String? url;
  final double size;
  final Widget? fallback;
  final String? semanticLabel;

  @override
  Widget build(BuildContext context) {
    final logo = url;
    if (logo == null) return fallback ?? const SizedBox.shrink();
    final image = CachedNetworkImage(
      imageUrl: logo,
      height: size,
      fit: BoxFit.contain,
      placeholder: (_, _) => SizedBox(width: size, height: size),
      errorWidget: (_, _, _) => fallback ?? const SizedBox.shrink(),
    );
    if (semanticLabel == null) return ExcludeSemantics(child: image);
    return Semantics(
      label: semanticLabel,
      image: true,
      child: ExcludeSemantics(child: image),
    );
  }
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
