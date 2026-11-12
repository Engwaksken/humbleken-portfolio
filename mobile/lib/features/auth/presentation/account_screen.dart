import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/router/routes.dart';
import '../../../core/widgets/brand.dart';
import '../../../core/widgets/layout.dart';
import '../../../core/widgets/paginated_list_view.dart';
import '../../../core/widgets/state_views.dart';
import '../../tickets/data/tickets_repository.dart';
import '../../tickets/presentation/ticket_tile.dart';
import '../data/auth_repository.dart';
import 'sign_in_form.dart';

class AccountScreen extends ConsumerWidget {
  const AccountScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authControllerProvider);
    final user = auth.value;

    final Widget body;
    if (user != null) {
      body = _SignedInView(user: user);
    } else if (auth.isLoading) {
      body = const LoadingView(label: 'Loading your account');
    } else if (auth.hasError) {
      body = Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          ErrorView(error: auth.error!, onRetry: () => ref.invalidate(authControllerProvider)),
          TextButton(
            onPressed: () => ref.read(authControllerProvider.notifier).signOut(),
            child: const Text('Sign out'),
          ),
        ],
      );
    } else {
      body = const SignInView();
    }

    return TopLevelScaffold(
      body: body,
      floatingActionButton: user != null && user.emailVerified
          ? FloatingActionButton.extended(
              onPressed: () => context.push(Routes.newTicket),
              icon: const Icon(Icons.add),
              label: const Text('New ticket'),
            )
          : null,
    );
  }
}

class _SignedInView extends ConsumerWidget {
  const _SignedInView({required this.user});

  final AppUser user;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final header = Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        _ProfileCard(user: user),
        if (!user.emailVerified) ...[const SizedBox(height: 16), const _VerifyEmailBanner()],
        const SizedBox(height: 24),
        const SectionHeading(title: 'Support tickets'),
      ],
    );

    if (!user.emailVerified) {
      return RefreshIndicator(
        onRefresh: () => ref.read(authControllerProvider.notifier).refreshUser().catchError((_) {}),
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
          children: [
            ContentWidth(child: header),
            const SizedBox(height: 12),
            const ContentWidth(child: Text('Verify your email address to open and view support tickets.')),
          ],
        ),
      );
    }

    final notifier = ref.read(ticketsProvider.notifier);
    return PaginatedListView(
      value: ref.watch(ticketsProvider),
      header: ContentWidth(child: header),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 96),
      separator: const SizedBox(height: 8),
      emptyMessage: 'You have no support tickets yet. Tap "New ticket" to ask for help.',
      itemBuilder: (context, ticket) => ContentWidth(child: TicketTile(ticket: ticket)),
      onLoadMore: notifier.loadMore,
      onRetryLoadMore: notifier.retryLoadMore,
      onRefresh: () async {
        try {
          ref.invalidate(ticketsProvider);
          await ref.read(ticketsProvider.future);
        } catch (_) {}
      },
    );
  }
}

class _ProfileCard extends ConsumerWidget {
  const _ProfileCard({required this.user});
  final AppUser user;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final theme = Theme.of(context);
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Wrap(
          alignment: WrapAlignment.spaceBetween,
          crossAxisAlignment: WrapCrossAlignment.center,
          runSpacing: 8,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Semantics(header: true, child: Text(user.name, style: theme.textTheme.titleLarge)),
                Text(
                  user.email,
                  style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                ),
              ],
            ),
            OutlinedButton.icon(
              onPressed: () async {
                final confirmed = await showDialog<bool>(
                  context: context,
                  builder: (context) => AlertDialog(
                    title: const Text('Sign out?'),
                    content: const Text('You will need to sign in again to see your tickets.'),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
                      FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Sign out')),
                    ],
                  ),
                );
                if (confirmed == true) await ref.read(authControllerProvider.notifier).signOut();
              },
              icon: const Icon(Icons.logout),
              label: const Text('Sign out'),
            ),
          ],
        ),
      ),
    );
  }
}

class _VerifyEmailBanner extends ConsumerStatefulWidget {
  const _VerifyEmailBanner();

  @override
  ConsumerState<_VerifyEmailBanner> createState() => _VerifyEmailBannerState();
}

class _VerifyEmailBannerState extends ConsumerState<_VerifyEmailBanner> {
  bool _resending = false;
  bool _refreshing = false;
  String? _status;

  Future<void> _resend() async {
    setState(() => _resending = true);
    try {
      final message = await ref.read(authRepositoryProvider).resendVerification();
      if (mounted) setState(() => _status = message);
    } catch (e) {
      if (mounted) setState(() => _status = ApiException.messageOf(e));
    } finally {
      if (mounted) setState(() => _resending = false);
    }
  }

  Future<void> _refresh() async {
    setState(() => _refreshing = true);
    try {
      await ref.read(authControllerProvider.notifier).refreshUser();
      final verified = ref.read(authControllerProvider).value?.emailVerified ?? false;
      if (mounted && !verified) {
        setState(
          () => _status = 'Your email address is not verified yet. Open the link in the email we sent, then try again.',
        );
      }
    } catch (e) {
      if (mounted) setState(() => _status = ApiException.messageOf(e));
    } finally {
      if (mounted) setState(() => _refreshing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final scheme = theme.colorScheme;
    return Semantics(
      container: true,
      child: Card(
        margin: EdgeInsets.zero,
        color: scheme.tertiaryContainer,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  ExcludeSemantics(child: Icon(Icons.mark_email_unread_outlined, color: scheme.onTertiaryContainer)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Semantics(
                      header: true,
                      child: Text(
                        'Verify your email address',
                        style: theme.textTheme.titleMedium?.copyWith(color: scheme.onTertiaryContainer),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                'We sent a verification link to your email. Open it to activate support tickets.',
                style: TextStyle(color: scheme.onTertiaryContainer),
              ),
              if (_status != null) ...[
                const SizedBox(height: 8),
                Semantics(
                  liveRegion: true,
                  child: Text(
                    _status!,
                    style: TextStyle(color: scheme.onTertiaryContainer, fontWeight: FontWeight.w600),
                  ),
                ),
              ],
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  FilledButton.tonal(
                    onPressed: _resending ? null : _resend,
                    child: Text(_resending ? 'Sending…' : 'Resend email'),
                  ),
                  FilledButton(
                    onPressed: _refreshing ? null : _refresh,
                    child: Text(_refreshing ? 'Checking…' : "I've verified, refresh"),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
