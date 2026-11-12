import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/utils/format.dart';
import '../../../core/widgets/layout.dart';
import '../../../core/widgets/state_views.dart';
import '../data/ticket_models.dart';
import '../data/tickets_repository.dart';
import 'ticket_tile.dart';

class TicketDetailScreen extends ConsumerWidget {
  const TicketDetailScreen({super.key, required this.id});

  final int id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final ticket = ref.watch(ticketDetailProvider(id));
    return Scaffold(
      appBar: AppBar(title: Text(ticket.value?.subject ?? 'Ticket #$id')),
      body: switch (ticket) {
        AsyncValue(:final value?) => _TicketThread(ticket: value),
        AsyncValue(:final error?) => ErrorView(error: error, onRetry: () => ref.invalidate(ticketDetailProvider(id))),
        _ => const LoadingView(label: 'Loading ticket'),
      },
    );
  }
}

class _TicketThread extends ConsumerWidget {
  const _TicketThread({required this.ticket});

  final TicketDetail ticket;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final theme = Theme.of(context);
    return Column(
      children: [
        Expanded(
          child: RefreshIndicator(
            onRefresh: () async {
              try {
                ref.invalidate(ticketDetailProvider(ticket.id));
                await ref.read(ticketDetailProvider(ticket.id).future);
              } catch (_) {}
            },
            child: ListView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
              children: [
                ContentWidth(
                  maxWidth: 760,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      SectionHeading(title: ticket.subject, level: 1),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 12,
                        runSpacing: 6,
                        crossAxisAlignment: WrapCrossAlignment.center,
                        children: [
                          TicketStatusChip(status: ticket.status, label: ticket.statusLabel),
                          if (ticket.createdAt != null)
                            Text(
                              'Opened ${formatDateTime(ticket.createdAt)}',
                              style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                            ),
                        ],
                      ),
                      const SizedBox(height: 20),
                      for (final reply in ticket.replies) ...[ReplyBubble(reply: reply), const SizedBox(height: 12)],
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        ContentWidth(maxWidth: 760, child: _ReplyComposer(ticket: ticket)),
      ],
    );
  }
}

/// One message in the thread: staff replies on the left in the secondary
/// colour, the customer's own on the right in the primary colour.
class ReplyBubble extends StatelessWidget {
  const ReplyBubble({super.key, required this.reply});

  final TicketReply reply;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final scheme = theme.colorScheme;
    final staff = reply.fromStaff;
    final bg = staff ? scheme.secondaryContainer : scheme.primaryContainer;
    final fg = staff ? scheme.onSecondaryContainer : scheme.onPrimaryContainer;
    final author = staff ? '${reply.authorName.isEmpty ? 'Support' : reply.authorName} (support team)' : 'You';

    return Align(
      alignment: staff ? Alignment.centerLeft : Alignment.centerRight,
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: MediaQuery.sizeOf(context).width * 0.85),
        child: MergeSemantics(
          child: Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: bg,
              borderRadius: BorderRadius.only(
                topLeft: const Radius.circular(16),
                topRight: const Radius.circular(16),
                bottomLeft: Radius.circular(staff ? 4 : 16),
                bottomRight: Radius.circular(staff ? 16 : 4),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    ExcludeSemantics(
                      child: Icon(staff ? Icons.support_agent : Icons.person_outline, size: 18, color: fg),
                    ),
                    const SizedBox(width: 6),
                    Flexible(
                      child: Text(
                        author,
                        style: theme.textTheme.labelLarge?.copyWith(color: fg, fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                SelectableText(reply.body, style: theme.textTheme.bodyLarge?.copyWith(color: fg)),
                if (reply.attachments.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  for (final file in reply.attachments)
                    Padding(
                      padding: const EdgeInsets.only(top: 4),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          ExcludeSemantics(child: Icon(Icons.attach_file, size: 16, color: fg)),
                          const SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              file.name,
                              semanticsLabel: 'Attachment: ${file.name}',
                              style: theme.textTheme.bodySmall?.copyWith(color: fg),
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
                if (reply.createdAt != null) ...[
                  const SizedBox(height: 6),
                  Text(formatDateTime(reply.createdAt), style: theme.textTheme.bodySmall?.copyWith(color: fg)),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _ReplyComposer extends ConsumerStatefulWidget {
  const _ReplyComposer({required this.ticket});
  final TicketDetail ticket;

  @override
  ConsumerState<_ReplyComposer> createState() => _ReplyComposerState();
}

class _ReplyComposerState extends ConsumerState<_ReplyComposer> {
  final _controller = TextEditingController();
  bool _busy = false;
  String? _error;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  String? _validate(String text) {
    final trimmed = text.trim();
    if (trimmed.length < 2) return 'Write at least 2 characters.';
    if (trimmed.length > 10000) return 'Replies can be at most 10,000 characters.';
    return null;
  }

  Future<void> _send() async {
    final error = _validate(_controller.text);
    if (error != null) {
      setState(() => _error = error);
      return;
    }
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      await ref.read(ticketDetailProvider(widget.ticket.id).notifier).reply(_controller.text);
      _controller.clear();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Reply sent.')));
      }
    } catch (e) {
      final message = e is ApiException ? (e.fieldError('body') ?? e.message) : ApiException.messageOf(e);
      if (mounted) setState(() => _error = message);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final closed = widget.ticket.isClosed;
    return Material(
      color: theme.colorScheme.surfaceContainer,
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 8, 12),
          child: closed
              ? Row(
                  children: [
                    const ExcludeSemantics(child: Icon(Icons.lock_outline)),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'This ticket is closed. Open a new ticket if you need more help.',
                        style: theme.textTheme.bodyMedium,
                      ),
                    ),
                  ],
                )
              : Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _controller,
                        enabled: !_busy,
                        minLines: 1,
                        maxLines: 5,
                        keyboardType: TextInputType.multiline,
                        textInputAction: TextInputAction.newline,
                        onChanged: (_) {
                          if (_error != null) setState(() => _error = null);
                        },
                        decoration: InputDecoration(labelText: 'Write a reply', errorText: _error, errorMaxLines: 3),
                      ),
                    ),
                    const SizedBox(width: 4),
                    IconButton.filled(
                      onPressed: _busy ? null : _send,
                      tooltip: 'Send reply',
                      icon: _busy
                          ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                          : const Icon(Icons.send),
                    ),
                  ],
                ),
        ),
      ),
    );
  }
}
