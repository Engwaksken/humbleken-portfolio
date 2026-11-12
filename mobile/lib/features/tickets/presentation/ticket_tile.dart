import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/routes.dart';
import '../../../core/utils/format.dart';
import '../../../core/widgets/tappable_card.dart';
import '../data/ticket_models.dart';

/// Coloured status label. Colours pair container/on-container roles so text
/// contrast follows the Material scheme.
class TicketStatusChip extends StatelessWidget {
  const TicketStatusChip({super.key, required this.status, required this.label});

  final TicketStatus status;
  final String label;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final (bg, fg, icon) = switch (status) {
      TicketStatus.open => (scheme.primaryContainer, scheme.onPrimaryContainer, Icons.mark_chat_unread_outlined),
      TicketStatus.answered => (scheme.tertiaryContainer, scheme.onTertiaryContainer, Icons.mark_chat_read_outlined),
      TicketStatus.closed => (scheme.surfaceContainerHighest, scheme.onSurfaceVariant, Icons.lock_outline),
      TicketStatus.unknown => (scheme.secondaryContainer, scheme.onSecondaryContainer, Icons.help_outline),
    };
    return Semantics(
      label: 'Status: $label',
      excludeSemantics: true,
      child: InfoPill(label: label, background: bg, foreground: fg, icon: icon),
    );
  }
}

class TicketTile extends StatelessWidget {
  const TicketTile({super.key, required this.ticket});

  final TicketSummary ticket;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final activity = ticket.lastActivityAt ?? ticket.createdAt;
    return TappableCard(
      onTap: () => context.push(Routes.ticket(ticket.id)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(ticket.subject, style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            Wrap(
              spacing: 12,
              runSpacing: 6,
              crossAxisAlignment: WrapCrossAlignment.center,
              children: [
                TicketStatusChip(status: ticket.status, label: ticket.statusLabel),
                if (activity != null)
                  Text(
                    'Last activity ${formatDateTime(activity)}',
                    style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
