import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:humbleken_portfolio/app.dart';
import 'package:humbleken_portfolio/core/api/paginated.dart';
import 'package:humbleken_portfolio/core/auth/token_store.dart';
import 'package:humbleken_portfolio/features/articles/data/article_models.dart';
import 'package:humbleken_portfolio/features/articles/data/articles_repository.dart';
import 'package:humbleken_portfolio/features/home/data/home_models.dart';
import 'package:humbleken_portfolio/features/pages/data/page_models.dart';
import 'package:humbleken_portfolio/features/pages/data/pages_repository.dart';
import 'package:humbleken_portfolio/features/projects/data/project_models.dart';
import 'package:humbleken_portfolio/features/projects/data/projects_repository.dart';
import 'package:humbleken_portfolio/features/services/data/service_models.dart';
import 'package:humbleken_portfolio/features/services/data/services_repository.dart';
import 'package:humbleken_portfolio/features/site/data/site_models.dart';
import 'package:humbleken_portfolio/features/site/data/site_repository.dart';

import '../fixtures.dart';

class _Pages implements PagesRepository {
  @override
  Future<HomePage> home() async => HomePage.fromJson(homePageJson());

  @override
  Future<PageContent> page(String key) async => PageContent.fromJson({
    'key': key,
    'eyebrow': key,
    'title': 'Page $key',
    'intro': 'Intro for $key',
    'body': 'Body text',
    'steps': [
      {'title': 'First', 'text': 'Do this', 'image_url': null},
      {'title': null, 'text': 'Then this', 'image_url': null},
    ],
    'gallery': <Object>[],
  });
}

class _Services implements ServicesRepository {
  @override
  Future<ServiceCatalogue> catalogue() async => ServiceCatalogue.fromJson({
    'data': [
      {
        'id': 3,
        'slug': 'network-setup',
        'name': 'Network setup',
        'summary': 'Reliable networks',
        'category': {'id': 1, 'name': 'Infrastructure', 'slug': 'infrastructure'},
      },
    ],
    'categories': [
      {'id': 1, 'name': 'Infrastructure', 'slug': 'infrastructure', 'description': 'Networks and servers'},
    ],
  });

  @override
  Future<ServiceDetail> detail(String slug) async => ServiceDetail.fromJson({
    'id': 3,
    'slug': slug,
    'name': 'Network setup',
    'description': 'Full description',
    'delivery_method': 'On site',
    'estimated_duration': '2 days',
    'indicative_price': 'From KES 20,000',
    'video_embed_url': 'https://www.youtube.com/embed/abc',
  });
}

class _Projects implements ProjectsRepository {
  @override
  Future<Paginated<ProjectSummary>> list({int page = 1}) async => Paginated.fromJson({
    'data': [
      {'id': page, 'slug': 'p$page', 'title': 'Project page $page', 'technologies': <String>[]},
    ],
    'meta': {'current_page': page, 'last_page': 2, 'per_page': 1, 'total': 2},
  }, ProjectSummary.fromJson);

  @override
  Future<ProjectDetail> detail(String slug) async => ProjectDetail.fromJson({
    'id': 1,
    'slug': slug,
    'title': 'Project',
    'body_markdown': '# Hello\n\n[link](https://x.test)',
  });
}

class _Articles implements ArticlesRepository {
  @override
  Future<List<Taxon>> categories() async => const [Taxon(name: 'Networking', slug: 'networking')];

  @override
  Future<ArticleDetail> detail(String slug) async =>
      ArticleDetail.fromJson({'id': 1, 'slug': slug, 'title': 'Article', 'body_markdown': 'Body'});

  @override
  Future<Paginated<ArticleSummary>> list({int page = 1, ArticleQuery query = const ArticleQuery()}) async =>
      Paginated.fromJson({
        'data': <Object>[],
        'meta': {'current_page': 1, 'last_page': 1, 'per_page': 10, 'total': 0},
      }, ArticleSummary.fromJson);
}

Future<void> pumpApp(WidgetTester tester, SiteConfig site) async {
  await tester.pumpWidget(
    ProviderScope(
      retry: (_, _) => null,
      overrides: [
        tokenStoreProvider.overrideWithValue(MemoryTokenStore()),
        siteProvider.overrideWith((ref) async => site),
        pagesRepositoryProvider.overrideWithValue(_Pages()),
        servicesRepositoryProvider.overrideWithValue(_Services()),
        projectsRepositoryProvider.overrideWithValue(_Projects()),
        articlesRepositoryProvider.overrideWithValue(_Articles()),
      ],
      child: const PortfolioApp(),
    ),
  );
  await tester.pumpAndSettle();
}

void main() {
  const site = SiteConfig(
    brandName: 'IT Problem Solver',
    contact: SiteContact(email: 'hello@example.test', whatsapp: '+254 700 000 000'),
    socialLinks: [SocialLink(platform: 'linkedin', url: 'https://linkedin.test/x')],
  );

  testWidgets('every tab and pushed screen renders without errors', (tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 3;
    addTearDown(tester.view.reset);

    await pumpApp(tester, site);
    expect(find.text('Turning complex IT challenges into simple solutions'), findsOneWidget);

    // Bottom navigation.
    await tester.tap(find.text('Services').last);
    await tester.pumpAndSettle();
    expect(find.text('Page services'), findsOneWidget);
    expect(find.text('Infrastructure'), findsOneWidget);

    await tester.tap(find.text('Network setup'));
    await tester.pumpAndSettle();
    expect(find.text('Request this service'), findsOneWidget);
    await tester.tap(find.text('Request this service'));
    await tester.pumpAndSettle();
    expect(find.text('Preferred date and time (required)'), findsOneWidget);
    expect(find.text('Network setup'), findsOneWidget, reason: 'service preselected in dropdown');

    // Validation runs client side before any request.
    await tester.ensureVisible(find.text('Request booking'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Request booking'));
    await tester.pumpAndSettle();
    expect(find.text('Enter your name.'), findsOneWidget);
    expect(find.text('Choose a preferred date and time.'), findsOneWidget);
    await tester.pageBack();
    await tester.pumpAndSettle();
    await tester.pageBack();
    await tester.pumpAndSettle();

    await tester.tap(find.text('Work'));
    await tester.pumpAndSettle();
    expect(find.text('Project page 1'), findsOneWidget);
    expect(find.text('Project page 2'), findsOneWidget, reason: 'second page loads via the footer');

    await tester.tap(find.text('Articles'));
    await tester.pumpAndSettle();
    expect(find.text('No articles have been published yet.'), findsOneWidget);
    expect(find.text('Networking'), findsOneWidget);

    await tester.tap(find.text('Account'));
    await tester.pumpAndSettle();
    expect(find.text('Create an account'), findsOneWidget);

    // Drawer -> Contact and Approach.
    await tester.tap(find.byTooltip('Open navigation menu'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Contact'));
    await tester.pumpAndSettle();
    expect(find.text('hello@example.test'), findsOneWidget);
    expect(find.text('LinkedIn'), findsOneWidget);
    await tester.pageBack();
    await tester.pumpAndSettle();

    await tester.tap(find.byTooltip('Open navigation menu'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Our approach'));
    await tester.pumpAndSettle();
    expect(find.text('Page approach'), findsOneWidget);
    expect(find.text('First'), findsOneWidget);

    expect(tester.takeException(), isNull);
  });

  testWidgets('hides Work and Articles when the features are off', (tester) async {
    await pumpApp(
      tester,
      const SiteConfig(brandName: 'IT Problem Solver', features: SiteFeatures(projects: false, articles: false)),
    );
    final bar = find.byType(NavigationBar);
    expect(find.descendant(of: bar, matching: find.text('Work')), findsNothing);
    expect(find.descendant(of: bar, matching: find.text('Articles')), findsNothing);
    expect(find.descendant(of: bar, matching: find.text('Account')), findsOneWidget);
  });

  testWidgets('shows the maintenance screen with a retry button', (tester) async {
    await pumpApp(
      tester,
      const SiteConfig(brandName: 'IT Problem Solver', maintenanceEnabled: true, maintenanceMessage: 'Back at 5pm'),
    );
    expect(find.text('Back at 5pm'), findsOneWidget);
    expect(find.text('Try again'), findsOneWidget);
    expect(find.byType(NavigationBar), findsNothing);
  });
}
