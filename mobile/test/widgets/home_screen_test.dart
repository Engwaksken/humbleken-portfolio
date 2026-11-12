import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:go_router/go_router.dart';
import 'package:humbleken_portfolio/core/api/api_client.dart';
import 'package:humbleken_portfolio/core/config/app_config.dart';
import 'package:humbleken_portfolio/features/home/data/home_models.dart';
import 'package:humbleken_portfolio/features/home/presentation/home_screen.dart';
import 'package:humbleken_portfolio/features/pages/data/page_models.dart';
import 'package:humbleken_portfolio/features/pages/data/pages_repository.dart';
import 'package:humbleken_portfolio/features/site/data/site_models.dart';
import 'package:humbleken_portfolio/features/site/data/site_repository.dart';

import '../fixtures.dart';

class FakePagesRepository implements PagesRepository {
  FakePagesRepository(this.homeJson);
  final Map<String, dynamic> homeJson;
  int homeCalls = 0;

  @override
  Future<HomePage> home() async {
    homeCalls++;
    return HomePage.fromJson(homeJson);
  }

  @override
  Future<PageContent> page(String key) async => PageContent(key: key, title: key);
}

Future<FakePagesRepository> pumpHome(
  WidgetTester tester, {
  required Map<String, dynamic> json,
  SiteConfig site = const SiteConfig(brandName: 'IT Problem Solver'),
}) async {
  final repo = FakePagesRepository(json);
  final router = GoRouter(
    initialLocation: '/home',
    routes: [
      GoRoute(path: '/home', builder: (_, _) => const HomeScreen()),
      GoRoute(
        path: '/services',
        builder: (_, _) => const Scaffold(body: Text('Services destination')),
      ),
      GoRoute(
        path: '/booking',
        builder: (_, _) => const Scaffold(body: Text('Booking destination')),
      ),
      GoRoute(
        path: '/contact',
        builder: (_, _) => const Scaffold(body: Text('Contact destination')),
      ),
      GoRoute(
        path: '/work',
        builder: (_, _) => const Scaffold(body: Text('Work destination')),
      ),
    ],
  );
  addTearDown(router.dispose);

  await tester.pumpWidget(
    ProviderScope(
      retry: (_, _) => null,
      overrides: [
        // Never touch the network in tests.
        appConfigProvider.overrideWithValue(const AppConfig(apiBaseUrl: 'http://localhost:1')),
        pagesRepositoryProvider.overrideWithValue(repo),
        siteProvider.overrideWith((ref) async => site),
      ],
      child: MaterialApp.router(routerConfig: router),
    ),
  );
  await tester.pumpAndSettle();
  return repo;
}

void main() {
  testWidgets('renders every enabled home section from the repository', (tester) async {
    final repo = await pumpHome(tester, json: homePageJson());

    expect(repo.homeCalls, 1);
    // App name in the app bar, whatever the site's brand name is.
    expect(find.text('HumbleKen'), findsOneWidget);
    expect(find.text('IT Problem Solver'), findsNothing);
    // Hero + aside card with numbered steps.
    expect(find.text('Turning complex IT challenges into simple solutions'), findsOneWidget);
    expect(find.text('Explore services'), findsOneWidget);
    expect(find.text('HOW IT WORKS'), findsOneWidget, reason: 'eyebrows are shown in capitals');
    expect(find.text('Tell us the problem'), findsOneWidget);
    expect(find.text('Send an enquiry'), findsOneWidget);
    // Services, why (with mapped icon), projects, CTA.
    expect(find.text('Network setup'), findsOneWidget);
    expect(find.text('Fast response'), findsOneWidget);
    expect(find.byIcon(Icons.bolt), findsOneWidget);
    expect(find.text('Clinic cloud migration'), findsOneWidget);
    expect(find.text('Ready to fix it?'), findsOneWidget);
  });

  testWidgets('skips sections with enabled: false', (tester) async {
    await pumpHome(tester, json: homePageJson(projectsEnabled: false));
    expect(find.text('Clinic cloud migration'), findsNothing);
    expect(find.text('Recent work'), findsNothing);
    expect(find.text('Network setup'), findsOneWidget);
  });

  testWidgets('hides projects when features.projects is false', (tester) async {
    await pumpHome(
      tester,
      json: homePageJson(),
      site: const SiteConfig(brandName: 'IT Problem Solver', features: SiteFeatures(projects: false)),
    );
    expect(find.text('Clinic cloud migration'), findsNothing);
    expect(find.text('Ready to fix it?'), findsOneWidget);
  });

  testWidgets('hero primary button opens Services', (tester) async {
    await pumpHome(tester, json: homePageJson());
    await tester.tap(find.text('Explore services'));
    await tester.pumpAndSettle();
    expect(find.text('Services destination'), findsOneWidget);
  });

  testWidgets('home screen meets tap target and labelling guidelines', (tester) async {
    final handle = tester.ensureSemantics();
    await pumpHome(tester, json: homePageJson());
    await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
    await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
    await expectLater(tester, meetsGuideline(textContrastGuideline));
    handle.dispose();
  });
}
