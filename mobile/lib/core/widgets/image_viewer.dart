import 'package:flutter/material.dart';

import 'app_network_image.dart';

/// Full-screen, pinch-to-zoom image viewer.
class ImageViewerScreen extends StatelessWidget {
  const ImageViewerScreen({super.key, required this.url, this.caption});

  final String url;
  final String? caption;

  static Future<void> open(BuildContext context, {required String url, String? caption}) {
    return Navigator.of(context, rootNavigator: true).push(
      MaterialPageRoute<void>(
        fullscreenDialog: true,
        builder: (_) => ImageViewerScreen(url: url, caption: caption),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final hasCaption = caption != null && caption!.trim().isNotEmpty;
    return Theme(
      data: ThemeData.dark(useMaterial3: true),
      child: Scaffold(
        backgroundColor: Colors.black,
        appBar: AppBar(
          backgroundColor: Colors.black,
          title: Text(hasCaption ? caption! : 'Image', maxLines: 1, overflow: TextOverflow.ellipsis),
          leading: IconButton(
            icon: const Icon(Icons.close),
            tooltip: 'Close image',
            onPressed: () => Navigator.of(context).pop(),
          ),
        ),
        body: SafeArea(
          child: Column(
            children: [
              Expanded(
                child: InteractiveViewer(
                  minScale: 1,
                  maxScale: 5,
                  child: Center(
                    child: AppNetworkImage(
                      url: url,
                      fit: BoxFit.contain,
                      semanticLabel: hasCaption ? caption : 'Image, pinch to zoom',
                    ),
                  ),
                ),
              ),
              if (hasCaption)
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Text(
                    caption!,
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: Colors.white),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
