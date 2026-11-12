import '../../../core/utils/json.dart';

class PageStep {
  const PageStep({this.title, required this.text, this.imageUrl});

  final String? title;
  final String text;
  final String? imageUrl;

  factory PageStep.fromJson(Json json) => PageStep(
    title: asStringOrNull(json['title']),
    text: asString(json['text']),
    imageUrl: asStringOrNull(json['image_url']),
  );
}

class GalleryImage {
  const GalleryImage({required this.imageUrl, this.caption});

  final String imageUrl;
  final String? caption;

  factory GalleryImage.fromJson(Json json) =>
      GalleryImage(imageUrl: asString(json['image_url']), caption: asStringOrNull(json['caption']));
}

/// Editable page header for `services`, `approach`, `booking` or `contact`.
class PageContent {
  const PageContent({
    required this.key,
    this.eyebrow,
    required this.title,
    this.intro,
    this.imageUrl,
    this.imageAlt = '',
    this.body,
    this.steps = const [],
    this.gallery = const [],
  });

  final String key;
  final String? eyebrow;
  final String title;
  final String? intro;
  final String? imageUrl;
  final String imageAlt;
  final String? body;
  final List<PageStep> steps;
  final List<GalleryImage> gallery;

  factory PageContent.fromJson(Json json) => PageContent(
    key: asString(json['key']),
    eyebrow: asStringOrNull(json['eyebrow']),
    title: asString(json['title']),
    intro: asStringOrNull(json['intro']),
    imageUrl: asStringOrNull(json['image_url']),
    imageAlt: asString(json['image_alt']),
    body: asStringOrNull(json['body']),
    steps: asList(json['steps'], PageStep.fromJson).where((s) => s.text.isNotEmpty || s.title != null).toList(),
    gallery: asList(json['gallery'], GalleryImage.fromJson).where((g) => g.imageUrl.isNotEmpty).toList(),
  );
}
