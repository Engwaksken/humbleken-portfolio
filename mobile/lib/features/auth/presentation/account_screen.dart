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
        const SizedBox(height: 16),
        _NotificationSettingsCard(user: user),
        const SizedBox(height: 16),
        const _AccountLinksCard(),
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

/// Email notification choices, saved to the website account.
class _NotificationSettingsCard extends ConsumerStatefulWidget {
  const _NotificationSettingsCard({required this.user});
  final AppUser user;

  @override
  ConsumerState<_NotificationSettingsCard> createState() => _NotificationSettingsCardState();
}

class _NotificationSettingsCardState extends ConsumerState<_NotificationSettingsCard> {
  bool _saving = false;

  Future<void> _toggle(bool enabled) async {
    setState(() => _saving = true);
    final messenger = ScaffoldMessenger.of(context);
    try {
      await ref.read(authControllerProvider.notifier).setTicketReplyEmails(enabled);
      messenger.showSnackBar(
        SnackBar(content: Text(enabled ? 'Ticket reply emails turned on' : 'Ticket reply emails turned off')),
      );
    } catch (e) {
      messenger.showSnackBar(SnackBar(content: Text(ApiException.messageOf(e))));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.zero,
      child: SwitchListTile(
        secondary: const Icon(Icons.notifications_outlined),
        title: const Text('Email me about replies to my support tickets'),
        subtitle: const Text('Important account emails, such as password resets, are always sent.'),
        value: widget.user.ticketReplyEmails,
        onChanged: _saving ? null : _toggle,
      ),
    );
  }
}

/// Privacy policy link and permanent account deletion (required by Google Play).
class _AccountLinksCard extends ConsumerWidget {
  const _AccountLinksCard();

  Future<void> _confirmDelete(BuildContext context) async {
    final deleted = await showDialog<bool>(context: context, builder: (_) => const _DeleteAccountDialog());
    if (deleted == true && context.mounted) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Your account and its data have been permanently deleted.')));
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final error = Theme.of(context).colorScheme.error;
    return Card(
      margin: EdgeInsets.zero,
      child: Column(
        children: [
          ListTile(
            leading: const Icon(Icons.privacy_tip_outlined),
            title: const Text('Privacy policy'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => context.push(Routes.privacy),
          ),
          const Divider(height: 1),
          ListTile(
            leading: Icon(Icons.delete_forever_outlined, color: error),
            title: Text('Delete my account', style: TextStyle(color: error)),
            subtitle: const Text('Permanently delete your account and its data'),
            onTap: () => _confirmDelete(context),
          ),
        ],
      ),
    );
  }
}

class _DeleteAccountDialog extends ConsumerStatefulWidget {
  const _DeleteAccountDialog();

  @override
  ConsumerState<_DeleteAccountDialog> createState() => _DeleteAccountDialogState();
}

class _DeleteAccountDialogState extends ConsumerState<_DeleteAccountDialog> {
  final _password = TextEditingController();
  bool _busy = false;
  String? _error;

  @override
  void dispose() {
    _password.dispose();
    super.dispose();
  }

  Future<void> _delete() async {
    if (_password.text.isEmpty) {
      setState(() => _error = 'Enter your password to confirm.');
      return;
    }
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      await ref.read(authControllerProvider.notifier).deleteAccount(_password.text);
      if (mounted) Navigator.pop(context, true);
    } on ApiException catch (e) {
      if (mounted) setState(() => _error = e.fieldError('password') ?? e.message);
    } catch (e) {
      if (mounted) setState(() => _error = ApiException.messageOf(e));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return AlertDialog(
      title: const Text('Delete your account?'),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'This permanently deletes your account, your support tickets and attachments, and the bookings '
              'and enquiries linked to your account. It cannot be undone.',
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _password,
              obscureText: true,
              autofocus: true,
              autofillHints: const [AutofillHints.password],
              decoration: InputDecoration(labelText: 'Your password', errorText: _error),
              onSubmitted: (_) => _busy ? null : _delete(),
            ),
          ],
        ),
      ),
      actions: [
        TextButton(onPressed: _busy ? null : () => Navigator.pop(context, false), child: const Text('Cancel')),
        FilledButton(
          style: FilledButton.styleFrom(backgroundColor: scheme.error, foregroundColor: scheme.onError),
          onPressed: _busy ? null : _delete,
          child: Text(_busy ? 'Deleting…' : 'Delete my account'),
        ),
      ],
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
          () => _status =
              'Your email address is not verified yet. Open the link in the confirmation email, then try again.',
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
                'A verification link has been sent to your email. Open it to activate support tickets.',
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
