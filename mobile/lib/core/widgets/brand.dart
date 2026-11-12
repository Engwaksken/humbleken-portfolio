import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../features/site/data/site_repository.dart';
import 'app_drawer.dart';

/// Brand logo; decorative (the brand name is always shown next to it).
/// Renders nothing if the image fails, e.g. an SVG logo.
class BrandLogo extends StatelessWidget {
  const BrandLogo({super.key, required this.url, this.size = 32});

  final String? url;
  final double size;

  @override
  Widget build(BuildContext context) {
    final logo = url;
    if (logo == null) return const SizedBox.shrink();
    return ExcludeSemantics(
      child: CachedNetworkImage(
        imageUrl: logo,
        height: size,
        fit: BoxFit.contain,
        placeholder: (_, _) => SizedBox(width: size, height: size),
        errorWidget: (_, _, _) => const SizedBox.shrink(),
      ),
    );
  }
}

/// App bar for top-level screens: logo + brand name and the drawer button.
class BrandAppBar extends ConsumerWidget implements PreferredSizeWidget {
  const BrandAppBar({super.key, this.actions, this.bottom});

  final List<Widget>? actions;
  final PreferredSizeWidget? bottom;

  @override
  Size get preferredSize => Size.fromHeight(kToolbarHeight + (bottom?.preferredSize.height ?? 0));

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final site = ref.watch(siteConfigProvider);
    return AppBar(
      titleSpacing: 0,
      title: Row(
        children: [
          BrandLogo(url: site.logoUrl),
          if (site.logoUrl != null) const SizedBox(width: 8),
          Flexible(child: Text(site.brandName, overflow: TextOverflow.ellipsis)),
        ],
      ),
      actions: actions,
      bottom: bottom,
    );
  }
}

/// Scaffold used by every top-level destination.
class TopLevelScaffold extends StatelessWidget {
  const TopLevelScaffold({super.key, required this.body, this.floatingActionButton, this.actions});

  final Widget body;
  final Widget? floatingActionButton;
  final List<Widget>? actions;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: BrandAppBar(actions: actions),
      drawer: const AppDrawer(),
      floatingActionButton: floatingActionButton,
      body: body,
    );
  }
}
