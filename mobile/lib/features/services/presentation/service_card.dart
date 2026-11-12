import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/routes.dart';
import '../../../core/widgets/app_network_image.dart';
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
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (showCategory && service.category != null)
                  Text(
                    service.category!.name,
                    style: theme.textTheme.labelMedium?.copyWith(color: theme.colorScheme.primary),
                  ),
                Text(service.name, style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
                if (service.summary != null) ...[
                  const SizedBox(height: 6),
                  Text(
                    service.summary!,
                    style: theme.textTheme.bodyMedium,
                    maxLines: 4,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
