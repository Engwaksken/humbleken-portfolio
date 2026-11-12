import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/routes.dart';
import '../../../core/widgets/app_network_image.dart';
import '../../../core/widgets/brand.dart';
import '../../../core/widgets/layout.dart';
import '../../../core/widgets/state_views.dart';
import '../../pages/data/pages_repository.dart';
import '../../projects/data/project_models.dart';
import '../../projects/presentation/project_card.dart';
import '../../services/data/service_models.dart';
import '../../services/presentation/service_card.dart';
import '../../site/data/site_repository.dart';
import '../data/home_models.dart';
import 'why_icons.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final home = ref.watch(homePageProvider);
    final features = ref.watch(siteConfigProvider.select((s) => s.features));

    Future<void> refresh() async {
      ref.invalidate(siteProvider);
      try {
        ref.invalidate(homePageProvider);
        await ref.read(homePageProvider.future);
      } catch (_) {
        // The error state is rendered from the provider.
      }
    }

    final Widget body;
    if (home.value case final page?) {
      body = RefreshIndicator(
        onRefresh: refresh,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
          children: [
            ContentWidth(
              child: HomeSections(page: page, showProjects: features.projects),
            ),
          ],
        ),
      );
    } else if (home.hasError) {
      body = ErrorView(error: home.error!, onRetry: () => ref.invalidate(homePageProvider));
    } else {
      body = const LoadingView(label: 'Loading home page');
    }

    return TopLevelScaffold(body: body);
  }
}

/// Renders every enabled section in API order: hero, services, why, projects, cta.
class HomeSections extends StatelessWidget {
  const HomeSections({super.key, required this.page, this.showProjects = true});

  final HomePage page;
  final bool showProjects;

  @override
  Widget build(BuildContext context) {
    const gap = SizedBox(height: 40);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (page.hero != null) HeroBlock(hero: page.hero!),
        if (page.services.isVisible) ...[gap, _ServicesBlock(section: page.services)],
        if (page.why.enabled && page.why.items.isNotEmpty) ...[gap, _WhyBlock(section: page.why)],
        if (showProjects && page.projects.isVisible) ...[gap, _ProjectsBlock(section: page.projects)],
        if (page.cta.isVisible) ...[gap, _CtaBlock(cta: page.cta)],
      ],
    );
  }
}

class HeroBlock extends StatelessWidget {
  const HeroBlock({super.key, required this.hero});

  final HeroSection hero;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final main = Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (hero.eyebrow != null)
          Text(
            hero.eyebrow!.toUpperCase(),
            style: theme.textTheme.labelLarge?.copyWith(color: theme.colorScheme.primary, letterSpacing: 1.1),
          ),
        const SizedBox(height: 4),
        Semantics(
          header: true,
          child: Text(hero.title, style: theme.textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.w700)),
        ),
        if (hero.intro != null) ...[const SizedBox(height: 12), Text(hero.intro!, style: theme.textTheme.bodyLarge)],
        const SizedBox(height: 20),
        Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            FilledButton(
              onPressed: () => context.go(Routes.services),
              child: Text(hero.primaryLabel ?? 'Explore services'),
            ),
            OutlinedButton(
              onPressed: () => context.push(Routes.booking),
              child: Text(hero.secondaryLabel ?? 'Book a consultation'),
            ),
          ],
        ),
      ],
    );

    final Widget? aside = switch (hero.aside) {
      HeroAside.image when hero.imageUrl != null => ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: AppNetworkImage(url: hero.imageUrl!, semanticLabel: hero.imageAlt, aspectRatio: 4 / 3),
      ),
      HeroAside.card when hero.card != null => HeroCardView(card: hero.card!),
      _ => null,
    };

    if (aside == null) return main;
    return LayoutBuilder(
      builder: (context, constraints) {
        if (constraints.maxWidth >= 720) {
          return Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(child: main),
              const SizedBox(width: 24),
              Expanded(child: aside),
            ],
          );
        }
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [main, const SizedBox(height: 24), aside],
        );
      },
    );
  }
}

class HeroCardView extends StatelessWidget {
  const HeroCardView({super.key, required this.card});

  final HeroCard card;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Card(
      margin: EdgeInsets.zero,
      color: theme.colorScheme.surfaceContainerLow,
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (card.eyebrow != null)
              Semantics(
                header: true,
                child: Text(
                  card.eyebrow!,
                  style: theme.textTheme.titleSmall?.copyWith(color: theme.colorScheme.primary),
                ),
              ),
            if (card.text != null) ...[const SizedBox(height: 8), Text(card.text!)],
            if (card.steps.isNotEmpty) ...[
              const SizedBox(height: 12),
              for (final (i, step) in card.steps.indexed)
                Padding(
                  padding: const EdgeInsets.only(bottom: 10),
                  child: MergeSemantics(
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        StepNumber(i + 1),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Padding(
                            padding: const EdgeInsets.only(top: 6),
                            child: Text(step, semanticsLabel: 'Step ${i + 1}: $step'),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
            ],
            const SizedBox(height: 4),
            TextButton.icon(
              onPressed: () => context.push(Routes.contact),
              icon: const Icon(Icons.arrow_forward),
              label: Text(card.linkLabel ?? 'Send an enquiry'),
            ),
          ],
        ),
      ),
    );
  }
}

class _ServicesBlock extends StatelessWidget {
  const _ServicesBlock({required this.section});
  final ListSection<ServiceSummary> section;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        SectionHeading(
          eyebrow: section.eyebrow,
          title: section.heading ?? 'Services',
          action: TextButton(
            onPressed: () => context.go(Routes.services),
            child: Text(section.linkLabel ?? 'All services'),
          ),
        ),
        const SizedBox(height: 12),
        ResponsiveGrid(children: [for (final s in section.items) ServiceCard(service: s)]),
      ],
    );
  }
}

class _WhyBlock extends StatelessWidget {
  const _WhyBlock({required this.section});
  final ListSection<WhyItem> section;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        SectionHeading(eyebrow: section.eyebrow, title: section.heading ?? 'Why work with us'),
        const SizedBox(height: 12),
        ResponsiveGrid(
          minItemWidth: 260,
          children: [
            for (final item in section.items)
              Card(
                margin: EdgeInsets.zero,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    if (item.imageUrl != null) AppNetworkImage(url: item.imageUrl!, aspectRatio: 16 / 9),
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          ExcludeSemantics(
                            child: CircleAvatar(
                              backgroundColor: theme.colorScheme.primaryContainer,
                              foregroundColor: theme.colorScheme.onPrimaryContainer,
                              child: Icon(iconForFontAwesome(item.icon)),
                            ),
                          ),
                          const SizedBox(height: 12),
                          if (item.heading.isNotEmpty) ...[
                            Semantics(
                              header: true,
                              child: Text(
                                item.heading,
                                style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
                              ),
                            ),
                            const SizedBox(height: 6),
                          ],
                          if (item.text != null) Text(item.text!),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
          ],
        ),
      ],
    );
  }
}

class _ProjectsBlock extends StatelessWidget {
  const _ProjectsBlock({required this.section});
  final ListSection<ProjectSummary> section;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        SectionHeading(
          eyebrow: section.eyebrow,
          title: section.heading ?? 'Recent work',
          action: TextButton(
            onPressed: () => context.go(Routes.work),
            child: Text(section.linkLabel ?? 'All projects'),
          ),
        ),
        const SizedBox(height: 12),
        ResponsiveGrid(children: [for (final p in section.items) ProjectCard(project: p)]),
      ],
    );
  }
}

class _CtaBlock extends StatelessWidget {
  const _CtaBlock({required this.cta});
  final CtaSection cta;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final scheme = theme.colorScheme;
    return Card(
      margin: EdgeInsets.zero,
      color: scheme.secondaryContainer,
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (cta.title != null)
              Semantics(
                header: true,
                child: Text(
                  cta.title!,
                  style: theme.textTheme.titleLarge?.copyWith(
                    color: scheme.onSecondaryContainer,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            if (cta.text != null) ...[
              const SizedBox(height: 8),
              Text(cta.text!, style: theme.textTheme.bodyLarge?.copyWith(color: scheme.onSecondaryContainer)),
            ],
            const SizedBox(height: 16),
            Wrap(
              spacing: 12,
              runSpacing: 12,
              children: [
                FilledButton(onPressed: () => context.push(Routes.booking), child: const Text('Book a consultation')),
                OutlinedButton(onPressed: () => context.push(Routes.contact), child: const Text('Contact us')),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
