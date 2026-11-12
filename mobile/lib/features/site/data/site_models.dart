import '../../../core/utils/json.dart';

class SiteContact {
  const SiteContact({this.email, this.phone, this.whatsapp, this.businessHours, this.address});

  final String? email;
  final String? phone;
  final String? whatsapp;
  final String? businessHours;
  final String? address;

  bool get isEmpty => email == null && phone == null && whatsapp == null && businessHours == null && address == null;

  factory SiteContact.fromJson(Json json) => SiteContact(
    email: asStringOrNull(json['email']),
    phone: asStringOrNull(json['phone']),
    whatsapp: asStringOrNull(json['whatsapp']),
    businessHours: asStringOrNull(json['business_hours']),
    address: asStringOrNull(json['address']),
  );
}

class SocialLink {
  const SocialLink({required this.platform, required this.url});

  final String platform;
  final String url;

  /// "linkedin" -> "LinkedIn", "github" -> "GitHub", otherwise capitalised.
  String get label => switch (platform.toLowerCase()) {
    'linkedin' => 'LinkedIn',
    'github' => 'GitHub',
    'gitlab' => 'GitLab',
    'youtube' => 'YouTube',
    'x' || 'twitter' => 'X (Twitter)',
    'tiktok' => 'TikTok',
    'whatsapp' => 'WhatsApp',
    _ => platform.isEmpty ? 'Link' : platform[0].toUpperCase() + platform.substring(1),
  };

  factory SocialLink.fromJson(Json json) =>
      SocialLink(platform: asString(json['platform']), url: asString(json['url']));
}

class SiteFeatures {
  const SiteFeatures({this.projects = true, this.articles = true, this.chatbot = false});

  final bool projects;
  final bool articles;
  final bool chatbot;

  factory SiteFeatures.fromJson(Json json) => SiteFeatures(
    projects: asBool(json['projects'], true),
    articles: asBool(json['articles'], true),
    chatbot: asBool(json['chatbot']),
  );
}

class SiteConfig {
  const SiteConfig({
    required this.brandName,
    this.tagline,
    this.logoUrl,
    this.faviconUrl,
    this.primaryHex = defaultPrimaryHex,
    this.secondaryHex = defaultSecondaryHex,
    this.contact = const SiteContact(),
    this.socialLinks = const [],
    this.features = const SiteFeatures(),
    this.maintenanceEnabled = false,
    this.maintenanceMessage,
    this.isFallback = false,
  });

  static const defaultPrimaryHex = '#077784';
  static const defaultSecondaryHex = '#14263d';
  static const defaultBrandName = 'HumbleKen';

  final String brandName;
  final String? tagline;
  final String? logoUrl;

  /// Favicon uploaded in the website admin; used as the chatbot's avatar.
  final String? faviconUrl;
  final String primaryHex;
  final String secondaryHex;
  final SiteContact contact;
  final List<SocialLink> socialLinks;
  final SiteFeatures features;
  final bool maintenanceEnabled;
  final String? maintenanceMessage;

  /// True when `/site` could not be loaded and built-in defaults are in use.
  final bool isFallback;

  static const fallback = SiteConfig(brandName: defaultBrandName, isFallback: true);

  factory SiteConfig.fromJson(Json json) {
    final theme = asMap(json['theme']);
    final maintenance = asMap(json['maintenance']);
    return SiteConfig(
      brandName: asStringOrNull(json['brand_name']) ?? defaultBrandName,
      tagline: asStringOrNull(json['tagline']),
      logoUrl: asStringOrNull(json['logo_url']),
      faviconUrl: asStringOrNull(json['favicon_url']),
      primaryHex: asStringOrNull(theme['primary']) ?? defaultPrimaryHex,
      secondaryHex: asStringOrNull(theme['secondary']) ?? defaultSecondaryHex,
      contact: SiteContact.fromJson(asMap(json['contact'])),
      socialLinks: asList(json['social_links'], SocialLink.fromJson).where((l) => l.url.isNotEmpty).toList(),
      features: SiteFeatures.fromJson(asMap(json['features'])),
      maintenanceEnabled: asBool(maintenance['enabled']),
      maintenanceMessage: asStringOrNull(maintenance['message']),
    );
  }
}
