import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/brand.dart';
import '../../../core/widgets/layout.dart';
import '../../../core/widgets/state_views.dart';
import '../../site/data/site_repository.dart';
import '../data/chat_repository.dart';

/// The Chatbot tab: a conversation with the site's AI assistant.
class ChatScreen extends ConsumerStatefulWidget {
  const ChatScreen({super.key});

  static const suggestions = [
    'My Wi-Fi keeps dropping. What can I check?',
    'Which of your services do I need?',
    'How do I book a consultation?',
  ];

  @override
  ConsumerState<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends ConsumerState<ChatScreen> {
  final _input = TextEditingController();
  final _scroll = ScrollController();
  final List<ChatMessage> _messages = [];
  bool _busy = false;
  String? _error;

  @override
  void dispose() {
    _input.dispose();
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _send([String? preset]) async {
    final text = (preset ?? _input.text).trim();
    if (text.isEmpty || _busy) return;
    setState(() {
      _messages.add(ChatMessage(ChatRole.user, text));
      _busy = true;
      _error = null;
      _input.clear();
    });
    _scrollToEnd();
    try {
      final reply = await ref.read(chatRepositoryProvider).send(_messages);
      if (!mounted) return;
      setState(() => _messages.add(ChatMessage(ChatRole.assistant, reply)));
    } catch (e) {
      if (!mounted) return;
      final notAvailable = e is ApiException && e.isNotFound;
      setState(() {
        // Put the question back so it can be retried.
        _messages.removeLast();
        _input.text = text;
        _error = notAvailable ? 'The assistant is not available right now.' : ApiException.messageOf(e);
      });
    } finally {
      if (mounted) setState(() => _busy = false);
      _scrollToEnd();
    }
  }

  void _clear() => setState(() {
    _messages.clear();
    _error = null;
  });

  void _scrollToEnd() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!_scroll.hasClients) return;
      final target = _scroll.position.maxScrollExtent;
      if (MediaQuery.disableAnimationsOf(context)) {
        _scroll.jumpTo(target);
      } else {
        _scroll.animateTo(target, duration: const Duration(milliseconds: 250), curve: Curves.easeOut);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final enabled = ref.watch(siteConfigProvider.select((s) => s.features.chatbot));

    return TopLevelScaffold(
      onBrand: true,
      // The shell scaffold already lifts the body above the keyboard.
      resizeToAvoidBottomInset: false,
      actions: [
        if (_messages.isNotEmpty)
          IconButton(
            tooltip: 'New conversation',
            onPressed: _busy ? null : _clear,
            icon: const Icon(Icons.refresh_rounded),
          ),
      ],
      body: !enabled
          ? const EmptyView(message: 'The assistant is not available right now.', icon: Icons.smart_toy_outlined)
          : Column(
              children: [
                Expanded(
                  child: ListView(
                    controller: _scroll,
                    padding: const EdgeInsets.fromLTRB(kPagePadding, 20, kPagePadding, 20),
                    children: [
                      ContentWidth(
                        maxWidth: 760,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            if (_messages.isEmpty)
                              _Welcome(onPick: _busy ? null : _send)
                            else
                              for (final m in _messages) _Bubble(message: m),
                            if (_busy) const _Typing(),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                _Composer(controller: _input, busy: _busy, error: _error, onSend: _send),
              ],
            ),
    );
  }
}

class _Welcome extends StatelessWidget {
  const _Welcome({required this.onPick});
  final ValueChanged<String>? onPick;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final scheme = theme.colorScheme;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        SurfaceCard(
          padding: const EdgeInsets.all(22),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const _BotAvatar(size: 52),
              const SizedBox(height: 16),
              Semantics(header: true, child: Text('Hi! How can I help?', style: theme.textTheme.headlineSmall)),
              const SizedBox(height: 8),
              Text(
                'Ask about our services or describe a technical problem and I will suggest next steps.',
                style: theme.textTheme.bodyMedium?.copyWith(color: scheme.onSurfaceVariant),
              ),
            ],
          ),
        ),
        const SizedBox(height: 22),
        const Eyebrow('Try asking'),
        const SizedBox(height: 10),
        for (final s in ChatScreen.suggestions) ...[
          SurfaceCard(
            padding: EdgeInsets.zero,
            child: ListTile(
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
              leading: Icon(Icons.chat_bubble_outline_rounded, color: scheme.primary),
              title: Text(s, style: theme.textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
              trailing: Icon(Icons.north_east_rounded, size: 18, color: scheme.onSurfaceVariant),
              onTap: onPick == null ? null : () => onPick!(s),
            ),
          ),
          const SizedBox(height: 10),
        ],
        const SizedBox(height: 6),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(Icons.info_outline_rounded, size: 16, color: scheme.onSurfaceVariant),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                'Answers are generated by AI and may be inaccurate. For anything important, '
                'send an enquiry or open a support ticket.',
                style: theme.textTheme.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

/// The chatbot's avatar: the favicon uploaded in the website admin, or the
/// bundled copy while it loads or when it is unavailable.
class _BotAvatar extends ConsumerWidget {
  const _BotAvatar({this.size = 32});
  final double size;

  static const bundledAsset = 'assets/brand/favicon.png';

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final url = ref.watch(siteConfigProvider.select((s) => s.faviconUrl));
    final scheme = Theme.of(context).colorScheme;
    final bundled = Image.asset(bundledAsset, fit: BoxFit.contain);
    return ExcludeSemantics(
      child: Container(
        width: size,
        height: size,
        padding: EdgeInsets.all(size * 0.12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(size * 0.3),
          border: Border.all(color: scheme.outlineVariant.withValues(alpha: 0.8)),
          boxShadow: BrandTokens.of(context).cardShadow,
        ),
        child: url == null
            ? bundled
            : CachedNetworkImage(
                imageUrl: url,
                fit: BoxFit.contain,
                placeholder: (_, _) => bundled,
                errorWidget: (_, _, _) => bundled,
              ),
      ),
    );
  }
}

class _Bubble extends StatelessWidget {
  const _Bubble({required this.message});
  final ChatMessage message;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final scheme = theme.colorScheme;
    final mine = message.role == ChatRole.user;
    const r = Radius.circular(18);
    final bubble = Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
      decoration: BoxDecoration(
        color: mine ? BrandTokens.of(context).deep : scheme.surfaceContainerLowest,
        borderRadius: BorderRadius.only(
          topLeft: r,
          topRight: r,
          bottomLeft: mine ? r : const Radius.circular(4),
          bottomRight: mine ? const Radius.circular(4) : r,
        ),
        border: mine ? null : Border.all(color: scheme.outlineVariant.withValues(alpha: 0.7)),
        boxShadow: mine ? null : BrandTokens.of(context).cardShadow,
      ),
      child: SelectableText(
        message.content,
        semanticsLabel: '${mine ? 'You' : 'Assistant'}: ${message.content}',
        style: theme.textTheme.bodyMedium?.copyWith(color: mine ? Colors.white : scheme.onSurface),
      ),
    );

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: mine ? MainAxisAlignment.end : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          if (!mine) ...[const _BotAvatar(), const SizedBox(width: 8)],
          Flexible(
            child: ConstrainedBox(
              constraints: BoxConstraints(maxWidth: MediaQuery.sizeOf(context).width * 0.78),
              child: bubble,
            ),
          ),
        ],
      ),
    );
  }
}

class _Typing extends StatelessWidget {
  const _Typing();

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Semantics(
      liveRegion: true,
      label: 'Assistant is replying',
      child: ExcludeSemantics(
        child: Row(
          children: [
            const _BotAvatar(),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: scheme.surfaceContainerLowest,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: scheme.outlineVariant.withValues(alpha: 0.7)),
              ),
              child: SizedBox(
                width: 16,
                height: 16,
                child: CircularProgressIndicator(strokeWidth: 2, color: scheme.primary),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Composer extends StatelessWidget {
  const _Composer({required this.controller, required this.busy, required this.error, required this.onSend});

  final TextEditingController controller;
  final bool busy;
  final String? error;
  final VoidCallback onSend;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return DecoratedBox(
      decoration: BoxDecoration(
        color: scheme.surfaceContainerLowest,
        border: Border(top: BorderSide(color: scheme.outlineVariant.withValues(alpha: 0.7))),
      ),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(kPagePadding - 4, 10, 10, 10),
        child: ContentWidth(
          maxWidth: 760,
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(
                child: TextField(
                  controller: controller,
                  minLines: 1,
                  maxLines: 4,
                  textCapitalization: TextCapitalization.sentences,
                  textInputAction: TextInputAction.send,
                  onSubmitted: (_) => onSend(),
                  decoration: InputDecoration(
                    hintText: 'Type your question',
                    errorText: error,
                    errorMaxLines: 3,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(24)),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(24),
                      borderSide: BorderSide(color: scheme.outlineVariant),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(24),
                      borderSide: BorderSide(color: scheme.primary, width: 2),
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Padding(
                padding: const EdgeInsets.only(bottom: 2),
                child: IconButton.filled(
                  onPressed: busy ? null : onSend,
                  tooltip: 'Send question',
                  iconSize: 22,
                  style: IconButton.styleFrom(minimumSize: const Size(50, 50)),
                  icon: const Icon(Icons.send_rounded),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
