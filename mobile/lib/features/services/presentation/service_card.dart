import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/routes.dart';
import '../../../core/widgets/app_network_image.dart';
import '../../../core/widgets/layout.dart';
import '../../../core/widgets/tappable_card.dart';
import '../data/service_models.dart';

class ServiceCard extends StatelessWidget {
  const ServiceCard({super.key, required this.service, this.showCategory = true});

  final ServiceSummary service;
  final bool showCategory;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return TappableCard(
      onTap: () => context.push(Routes.service(service.slug)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (service.imageUrl != null) AppNetworkImage(url: service.imageUrl!, aspectRatio: 16 / 9),
          Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (service.imageUrl == null) ...[
                  const IconBadge(icon: Icons.design_services_rounded, size: 44),
                  const SizedBox(height: 14),
                ],
                if (showCategory && service.category != null) ...[
                  Eyebrow(service.category!.name),
                  const SizedBox(height: 4),
                ],
                Text(service.name, style: theme.textTheme.titleMedium?.copyWith(fontSize: 17)),
                if (service.summary != null) ...[
                  const SizedBox(height: 6),
                  Text(
                    service.summary!,
                    style: theme.textTheme.bodyMedium,
                    maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
                const SizedBox(height: 14),
                ExcludeSemantics(
                  child: Row(
                    children: [
                      Text('Learn more', style: theme.textTheme.labelLarge?.copyWith(color: theme.colorScheme.primary)),
                      const SizedBox(width: 4),
                      Icon(Icons.arrow_forward_rounded, size: 18, color: theme.colorScheme.primary),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
