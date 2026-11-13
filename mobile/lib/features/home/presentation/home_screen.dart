import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/routes.dart';
import '../../../core/theme/app_theme.dart';
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
          padding: const EdgeInsets.only(bottom: 40),
          children: [HomeSections(page: page, showProjects: features.projects)],
        ),
      );
    } else if (home.hasError) {
      body = ErrorView(error: home.error!, onRetry: () => ref.invalidate(homePageProvider));
    } else {
      body = const LoadingView(label: 'Loading home page');
    }

    return TopLevelScaffold(onBrand: true, body: body);
  }
}

/// Renders every enabled section in API order: hero, services, why, projects, cta.
class HomeSections extends StatelessWidget {
  const HomeSections({super.key, required this.page, this.showProjects = true});

  final HomePage page;
  final bool showProjects;

  @override
  Widget build(BuildContext context) {
    const gap = SizedBox(height: 36);
    Widget padded(Widget child) => Padding(
      padding: const EdgeInsets.symmetric(horizontal: kPagePadding),
      child: ContentWidth(child: child),
    );

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (page.hero != null) HeroBlock(hero: page.hero!) else const SizedBox(height: 20),
        padded(const _QuickActions()),
        if (page.services.isVisible) ...[gap, _ServicesBlock(section: page.services)],
        if (page.why.enabled && page.why.items.isNotEmpty) ...[gap, padded(_WhyBlock(section: page.why))],
        if (showProjects && page.projects.isVisible) ...[gap, padded(_ProjectsBlock(section: page.projects))],
        if (page.cta.isVisible) ...[gap, padded(_CtaBlock(cta: page.cta))],
      ],
    );
  }
}

/// Gradient header with the title and buttons; the getting-started card or
/// hero image overlaps its lower edge.
class HeroBlock extends StatelessWidget {
  const HeroBlock({super.key, required this.hero});

  final HeroSection hero;

  @override
  Widget build(BuildContext context) {
    final Widget? aside = switch (hero.aside) {
      HeroAside.image when hero.imageUrl != null => DecoratedBox(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(AppTheme.radius),
          boxShadow: BrandTokens.of(context).cardShadow,
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(AppTheme.radius),
          child: AppNetworkImage(url: hero.imageUrl!, semanticLabel: hero.imageAlt, aspectRatio: 16 / 10),
        ),
      ),
      HeroAside.card when hero.card != null => HeroCardView(card: hero.card!),
      _ => null,
    };

    const overlap = 40.0;
    final header = GradientHeader(
      eyebrow: hero.eyebrow,
      title: hero.title,
      intro: hero.intro,
      bottomInset: aside == null ? 36 : 36 + overlap,
      actions: [
        FilledButton.icon(
          style: onBrandFilledStyle(context),
          onPressed: () => context.go(Routes.services),
          icon: const Icon(Icons.arrow_forward_rounded, size: 20),
          label: Text(hero.primaryLabel ?? 'Explore services'),
        ),
        OutlinedButton(
          style: onBrandOutlinedStyle(context),
          onPressed: () => context.push(Routes.booking),
          child: Text(hero.secondaryLabel ?? 'Book a consultation'),
        ),
      ],
    );

    if (aside == null) return Padding(padding: const EdgeInsets.only(bottom: 20), child: header);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        header,
        Transform.translate(
          offset: const Offset(0, -overlap),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: kPagePadding),
            child: ContentWidth(child: aside),
          ),
        ),
        // The translated card leaves `overlap` px of layout space below it,
        // which doubles as the gap before the next section.
      ],
    );
  }
}

class HeroCardView extends StatelessWidget {
  const HeroCardView({super.key, required this.card});

  final HeroCard card;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return SurfaceCard(
      padding: const EdgeInsets.all(22),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (card.eyebrow != null) Semantics(header: true, child: Eyebrow(card.eyebrow!)),
          if (card.text != null) ...[
            const SizedBox(height: 8),
            Text(card.text!, style: theme.textTheme.titleMedium?.copyWith(height: 1.4)),
          ],
          if (card.steps.isNotEmpty) ...[
            const SizedBox(height: 16),
            for (final (i, step) in card.steps.indexed)
              Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: MergeSemantics(
                  child: Row(
                    children: [
                      StepNumber(i + 1),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Text(
                          step,
                          semanticsLabel: 'Step ${i + 1}: $step',
                          style: theme.textTheme.bodyMedium?.copyWith(
                            color: theme.colorScheme.onSurface,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
          ],
          const Divider(height: 20),
          SectionLink(label: card.linkLabel ?? 'Send an enquiry', onPressed: () => context.push(Routes.contact)),
        ],
      ),
    );
  }
}

/// Three shortcut tiles to the main conversion screens.
class _QuickActions extends StatelessWidget {
  const _QuickActions();

  @override
  Widget build(BuildContext context) {
    final actions = [
      (icon: Icons.event_available_rounded, label: 'Book a call', route: Routes.booking),
      (icon: Icons.mail_outline_rounded, label: 'Send a message', route: Routes.contact),
      (icon: Icons.route_rounded, label: 'How I work', route: Routes.approach),
    ];
    final theme = Theme.of(context);
    return Row(
      children: [
        for (final (i, action) in actions.indexed) ...[
          if (i > 0) const SizedBox(width: 12),
          Expanded(
            child: Semantics(
              button: true,
              excludeSemantics: true,
              label: action.label,
              child: Material(
                color: theme.colorScheme.surfaceContainerLowest,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(18),
                  side: BorderSide(color: theme.colorScheme.outlineVariant),
                ),
                clipBehavior: Clip.antiAlias,
                child: InkWell(
                  onTap: () => context.push(action.route),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
                    child: Column(
                      children: [
                        IconBadge(icon: action.icon, size: 44),
                        const SizedBox(height: 10),
                        Text(
                          action.label,
                          textAlign: TextAlign.center,
                          style: theme.textTheme.labelLarge?.copyWith(color: theme.colorScheme.onSurface),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ],
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
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: kPagePadding),
          child: ContentWidth(
            child: SectionHeading(
              eyebrow: section.eyebrow,
              title: section.heading ?? 'Services',
              action: SectionLink(
                label: section.linkLabel ?? 'All services',
                onPressed: () => context.go(Routes.services),
              ),
            ),
          ),
        ),
        const SizedBox(height: 16),
        // Swipeable carousel; the cards keep their natural height.
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.fromLTRB(kPagePadding, 4, kPagePadding, 16),
          clipBehavior: Clip.none,
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              for (final (i, s) in section.items.indexed) ...[
                if (i > 0) const SizedBox(width: 14),
                SizedBox(width: 280, child: ServiceCard(service: s)),
              ],
            ],
          ),
        ),
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
        SectionHeading(eyebrow: section.eyebrow, title: section.heading ?? 'Why work with me'),
        const SizedBox(height: 16),
        ResponsiveGrid(
          minItemWidth: 280,
          children: [
            for (final item in section.items)
              SurfaceCard(
                padding: EdgeInsets.zero,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    if (item.imageUrl != null) AppNetworkImage(url: item.imageUrl!, aspectRatio: 16 / 9),
                    Padding(
                      padding: const EdgeInsets.all(18),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (item.imageUrl == null) ...[
                            IconBadge(icon: iconForFontAwesome(item.icon)),
                            const SizedBox(width: 16),
                          ],
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                if (item.heading.isNotEmpty) ...[
                                  Semantics(
                                    header: true,
                                    child: Text(item.heading, style: theme.textTheme.titleMedium),
                                  ),
                                  const SizedBox(height: 4),
                                ],
                                if (item.text != null) Text(item.text!, style: theme.textTheme.bodyMedium),
                              ],
                            ),
                          ),
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
          action: SectionLink(label: section.linkLabel ?? 'All projects', onPressed: () => context.go(Routes.work)),
        ),
        const SizedBox(height: 16),
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
    return Container(
      decoration: BoxDecoration(
        gradient: BrandTokens.of(context).heroGradient,
        borderRadius: BorderRadius.circular(24),
      ),
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const IconBadge(icon: Icons.support_agent_rounded, onDark: true),
          const SizedBox(height: 16),
          if (cta.title != null)
            Semantics(
              header: true,
              child: Text(cta.title!, style: theme.textTheme.headlineSmall?.copyWith(color: Colors.white)),
            ),
          if (cta.text != null) ...[
            const SizedBox(height: 8),
            Text(cta.text!, style: theme.textTheme.bodyLarge?.copyWith(color: Colors.white.withValues(alpha: 0.85))),
          ],
          const SizedBox(height: 20),
          Wrap(
            spacing: 12,
            runSpacing: 12,
            children: [
              FilledButton(
                style: onBrandFilledStyle(context),
                onPressed: () => context.push(Routes.booking),
                child: const Text('Book a consultation'),
              ),
              OutlinedButton(
                style: onBrandOutlinedStyle(context),
                onPressed: () => context.push(Routes.contact),
                child: const Text('Contact me'),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
