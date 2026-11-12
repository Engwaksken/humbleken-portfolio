import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

/// Cached network image with consistent placeholder/error states and an
/// accessibility label. Pass an empty [semanticLabel] for decorative images.
class AppNetworkImage extends StatelessWidget {
  const AppNetworkImage({
    super.key,
    required this.url,
    this.semanticLabel,
    this.fit = BoxFit.cover,
    this.width,
    this.height,
    this.aspectRatio,
  });

  final String url;
  final String? semanticLabel;
  final BoxFit fit;
  final double? width;
  final double? height;

  /// When set, the image is laid out in an [AspectRatio] box.
  final double? aspectRatio;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    Widget image = CachedNetworkImage(
      imageUrl: url,
      fit: fit,
      width: width,
      height: height,
      fadeInDuration: MediaQuery.disableAnimationsOf(context) ? Duration.zero : const Duration(milliseconds: 250),
      placeholder: (context, _) => ColoredBox(color: scheme.surfaceContainerHighest),
      errorWidget: (context, _, _) => ColoredBox(
        color: scheme.surfaceContainerHighest,
        child: Center(child: Icon(Icons.broken_image_outlined, color: scheme.onSurfaceVariant)),
      ),
    );
    if (aspectRatio != null) image = AspectRatio(aspectRatio: aspectRatio!, child: image);

    final label = semanticLabel;
    if (label == null || label.trim().isEmpty) return ExcludeSemantics(child: image);
    return Semantics(
      image: true,
      label: label,
      child: ExcludeSemantics(child: image),
    );
  }
}
