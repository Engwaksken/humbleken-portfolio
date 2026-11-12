import '../../../core/utils/json.dart';
import '../../projects/data/project_models.dart';
import '../../services/data/service_models.dart';

enum HeroAside {
  card,
  image,
  none;

  static HeroAside parse(Object? value) => switch (value) {
    'card' => HeroAside.card,
    'image' => HeroAside.image,
    _ => HeroAside.none,
  };
}

class HeroCard {
  const HeroCard({this.eyebrow, this.text, this.steps = const [], this.linkLabel});

  final String? eyebrow;
  final String? text;
  final List<String> steps;
  final String? linkLabel;

  factory HeroCard.fromJson(Json json) => HeroCard(
    eyebrow: asStringOrNull(json['eyebrow']),
    text: asStringOrNull(json['text']),
    steps: asStringList(json['steps']).where((s) => s.trim().isNotEmpty).toList(),
    linkLabel: asStringOrNull(json['link_label']),
  );
}

class HeroSection {
  const HeroSection({
    this.eyebrow,
    required this.title,
    this.intro,
    this.primaryLabel,
    this.secondaryLabel,
    this.aside = HeroAside.none,
    this.imageUrl,
    this.imageAlt = '',
    this.card,
  });

  final String? eyebrow;
  final String title;
  final String? intro;
  final String? primaryLabel;
  final String? secondaryLabel;
  final HeroAside aside;
  final String? imageUrl;
  final String imageAlt;
  final HeroCard? card;

  factory HeroSection.fromJson(Json json) {
    final card = asMapOrNull(json['card']);
    return HeroSection(
      eyebrow: asStringOrNull(json['eyebrow']),
      title: asString(json['title']),
      intro: asStringOrNull(json['intro']),
      primaryLabel: asStringOrNull(json['primary_label']),
      secondaryLabel: asStringOrNull(json['secondary_label']),
      aside: HeroAside.parse(json['aside']),
      imageUrl: asStringOrNull(json['image_url']),
      imageAlt: asString(json['image_alt']),
      card: card == null ? null : HeroCard.fromJson(card),
    );
  }
}

/// A list section (`services`, `projects`) with heading and items.
class ListSection<T> {
  const ListSection({this.enabled = true, this.eyebrow, this.heading, this.linkLabel, this.items = const []});

  final bool enabled;
  final String? eyebrow;
  final String? heading;
  final String? linkLabel;
  final List<T> items;

  bool get isVisible => enabled && items.isNotEmpty;

  factory ListSection.fromJson(Json json, T Function(Json) parse) => ListSection(
    enabled: asBool(json['enabled'], true),
    eyebrow: asStringOrNull(json['eyebrow']),
    heading: asStringOrNull(json['heading']),
    linkLabel: asStringOrNull(json['link_label']),
    items: asList(json['items'], parse),
  );
}

class WhyItem {
  const WhyItem({required this.icon, required this.heading, this.text, this.imageUrl});

  final String icon;
  final String heading;
  final String? text;
  final String? imageUrl;

  factory WhyItem.fromJson(Json json) => WhyItem(
    icon: asString(json['icon']),
    heading: asString(json['heading']),
    text: asStringOrNull(json['text']),
    imageUrl: asStringOrNull(json['image_url']),
  );
}

class CtaSection {
  const CtaSection({this.enabled = true, this.title, this.text});

  final bool enabled;
  final String? title;
  final String? text;

  bool get isVisible => enabled && (title != null || text != null);

  factory CtaSection.fromJson(Json json) => CtaSection(
    enabled: asBool(json['enabled'], true),
    title: asStringOrNull(json['title']),
    text: asStringOrNull(json['text']),
  );
}

class HomePage {
  const HomePage({
    this.hero,
    this.services = const ListSection(enabled: false),
    this.why = const ListSection(enabled: false),
    this.projects = const ListSection(enabled: false),
    this.cta = const CtaSection(enabled: false),
  });

  final HeroSection? hero;
  final ListSection<ServiceSummary> services;
  final ListSection<WhyItem> why;
  final ListSection<ProjectSummary> projects;
  final CtaSection cta;

  factory HomePage.fromJson(Json json) {
    final hero = asMapOrNull(json['hero']);
    final services = asMapOrNull(json['services']);
    final why = asMapOrNull(json['why']);
    final projects = asMapOrNull(json['projects']);
    final cta = asMapOrNull(json['cta']);
    return HomePage(
      hero: hero == null ? null : HeroSection.fromJson(hero),
      services: services == null
          ? const ListSection(enabled: false)
          : ListSection.fromJson(services, ServiceSummary.fromJson),
      why: why == null ? const ListSection(enabled: false) : ListSection.fromJson(why, WhyItem.fromJson),
      projects: projects == null
          ? const ListSection(enabled: false)
          : ListSection.fromJson(projects, ProjectSummary.fromJson),
      cta: cta == null ? const CtaSection(enabled: false) : CtaSection.fromJson(cta),
    );
  }
}
