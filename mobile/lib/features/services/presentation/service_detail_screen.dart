import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/routes.dart';
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
      appBar: AppBar(title: Text(service.value?.name ?? 'Service')),
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

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        ContentWidth(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              SectionHeading(eyebrow: service.category?.name, title: service.name, level: 1),
              if (service.summary != null) ...[
                const SizedBox(height: 12),
                Text(service.summary!, style: theme.textTheme.bodyLarge),
              ],
              if (service.imageUrl != null) ...[
                const SizedBox(height: 16),
                ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: AppNetworkImage(url: service.imageUrl!, aspectRatio: 16 / 9),
                ),
              ],
              if (facts.isNotEmpty) ...[
                const SizedBox(height: 16),
                Card(
                  margin: EdgeInsets.zero,
                  child: Column(
                    children: [
                      for (final (icon, label, value) in facts)
                        ListTile(leading: Icon(icon), title: Text(label), subtitle: Text(value!)),
                    ],
                  ),
                ),
              ],
              if (service.description != null) ...[
                const SizedBox(height: 16),
                Text(service.description!, style: theme.textTheme.bodyLarge),
              ],
              const SizedBox(height: 24),
              FilledButton.icon(
                onPressed: () => context.push(Routes.bookingFor(service.id)),
                icon: const Icon(Icons.event_available),
                label: const Text('Request this service'),
              ),
              if (service.videoEmbedUrl != null) ...[
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: () => openExternalUrl(context, watchUrlFromEmbed(service.videoEmbedUrl!)),
                  icon: const Icon(Icons.play_circle_outline),
                  label: const Text('Watch the video'),
                ),
              ],
            ],
          ),
        ),
      ],
    );
  }
}
