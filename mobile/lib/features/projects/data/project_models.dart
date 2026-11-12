import '../../../core/utils/json.dart';

class ProjectSummary {
  const ProjectSummary({
    required this.id,
    required this.slug,
    required this.title,
    this.summary,
    this.technologies = const [],
    this.imageUrl,
    this.publishedAt,
  });

  final int id;
  final String slug;
  final String title;
  final String? summary;
  final List<String> technologies;
  final String? imageUrl;
  final DateTime? publishedAt;

  factory ProjectSummary.fromJson(Json json) => ProjectSummary(
    id: asInt(json['id']),
    slug: asString(json['slug']),
    title: asString(json['title']),
    summary: asStringOrNull(json['summary']),
    technologies: asStringList(json['technologies']),
    imageUrl: asStringOrNull(json['image_url']),
    publishedAt: asDateTime(json['published_at']),
  );
}

class ProjectDetail extends ProjectSummary {
  const ProjectDetail({
    required super.id,
    required super.slug,
    required super.title,
    super.summary,
    super.technologies,
    super.imageUrl,
    super.publishedAt,
    this.bodyMarkdown,
    this.videoEmbedUrl,
  });

  final String? bodyMarkdown;
  final String? videoEmbedUrl;

  factory ProjectDetail.fromJson(Json json) {
    final base = ProjectSummary.fromJson(json);
    return ProjectDetail(
      id: base.id,
      slug: base.slug,
      title: base.title,
      summary: base.summary,
      technologies: base.technologies,
      imageUrl: base.imageUrl,
      publishedAt: base.publishedAt,
      bodyMarkdown: asStringOrNull(json['body_markdown']),
      videoEmbedUrl: asStringOrNull(json['video_embed_url']),
    );
  }
}
