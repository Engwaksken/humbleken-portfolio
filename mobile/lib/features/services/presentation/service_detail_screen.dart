import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/routes.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/links.dart';
import '../../../core/widgets/app_network_image.dart';
import '../../../core/widgets/layout.dart';
import '../../../core/widgets/state_views.dart';
import '../data/service_models.dart';
import '../data/services_repository.dart';

class ServiceDetailScreen extends ConsumerWidget {
  const ServiceDetailScreen({super.key, required this.slug});

  final String slug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final service = ref.watch(serviceDetailProvider(slug));
    return Scaffold(
      appBar: brandedAppBar(context),
      body: switch (service) {
        AsyncValue(:final value?) => _ServiceBody(service: value),
        AsyncValue(:final error?) => ErrorView(
          error: error,
          onRetry: () => ref.invalidate(serviceDetailProvider(slug)),
        ),
        _ => const LoadingView(label: 'Loading service'),
      },
    );
  }
}

class _ServiceBody extends StatelessWidget {
  const _ServiceBody({required this.service});

  final ServiceDetail service;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final facts = [
      (Icons.handshake_outlined, 'Delivery', service.deliveryMethod),
      (Icons.schedule, 'Estimated duration', service.estimatedDuration),
      (Icons.payments_outlined, 'Indicative price', service.indicativePrice),
    ].where((f) => f.$3 != null).toList();

    return BrandedPageBody(
      header: GradientHeader(eyebrow: service.category?.name ?? 'Service', title: service.name, intro: service.summary),
      children: [
        if (service.imageUrl != null) ...[
          ClipRRect(
            borderRadius: BorderRadius.circular(AppTheme.radius),
            child: AppNetworkImage(url: service.imageUrl!, aspectRatio: 16 / 9),
          ),
          const SizedBox(height: 20),
        ],
        if (facts.isNotEmpty) ...[
          SurfaceCard(
            padding: const EdgeInsets.symmetric(vertical: 6),
            child: Column(
              children: [
                for (final (i, (icon, label, value)) in facts.indexed) ...[
                  if (i > 0) const Divider(height: 1, indent: 76),
                  ListTile(
                    contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 4),
                    leading: IconBadge(icon: icon, size: 42),
                    title: Text(label, style: theme.textTheme.bodySmall),
                    subtitle: Text(value!, style: theme.textTheme.titleMedium),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 20),
        ],
        if (service.description != null) ...[
          const SectionHeading(title: 'Overview'),
          const SizedBox(height: 10),
          Text(service.description!, style: theme.textTheme.bodyLarge),
          const SizedBox(height: 28),
        ],
        FilledButton.icon(
          onPressed: () => context.push(Routes.bookingFor(service.id)),
          icon: const Icon(Icons.event_available_rounded),
          label: const Text('Request this service'),
        ),
        if (service.videoEmbedUrl != null) ...[
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: () => openExternalUrl(context, watchUrlFromEmbed(service.videoEmbedUrl!)),
            icon: const Icon(Icons.play_circle_outline_rounded),
            label: const Text('Watch the video'),
          ),
        ],
      ],
    );
  }
}
