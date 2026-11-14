import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/widgets/layout.dart';
import '../../site/data/site_models.dart';
import '../../site/data/site_repository.dart';

/// Bank transfer and mobile money details from Settings, Payments on the website.
/// Hidden when no manual payment option is switched on.
class PaymentDetails extends ConsumerWidget {
  const PaymentDetails({super.key, this.topSpacing = 28});

  final double topSpacing;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final PaymentOptions payments = ref.watch(siteConfigProvider).payments;
    if (payments.isEmpty) return const SizedBox.shrink();

    final bank = payments.bank;
    final mobileMoney = payments.mobileMoney;

    return Padding(
      padding: EdgeInsets.only(top: topSpacing),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SectionHeading(
            title: 'Payment options',
            subtitle: 'Prices are in ${ref.watch(siteConfigProvider).currency.code}. Tap a number to copy it.',
          ),
          const SizedBox(height: 8),
          Card(
            margin: EdgeInsets.zero,
            child: Column(
              children: [
                if (bank != null) ...[
                  const _GroupTitle(icon: Icons.account_balance_outlined, title: 'Bank transfer'),
                  _DetailTile(label: 'Bank', value: bank.bankName),
                  _DetailTile(label: 'Account name', value: bank.accountName),
                  _DetailTile(label: 'Account number', value: bank.accountNumber, copyable: true),
                  _DetailTile(label: 'Branch', value: bank.branch),
                  _DetailTile(label: 'SWIFT code', value: bank.swift, copyable: true),
                ],
                if (bank != null && mobileMoney != null) const Divider(height: 1),
                if (mobileMoney != null) ...[
                  _GroupTitle(icon: Icons.phone_android_outlined, title: mobileMoney.provider ?? 'Mobile money'),
                  _DetailTile(label: 'Number', value: mobileMoney.number, copyable: true),
                  _DetailTile(label: 'Registered name', value: mobileMoney.name),
                ],
                if (payments.instructions != null) ...[
                  if (bank != null || mobileMoney != null) const Divider(height: 1),
                  Padding(padding: const EdgeInsets.all(16), child: Text(payments.instructions!)),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _GroupTitle extends StatelessWidget {
  const _GroupTitle({required this.icon, required this.title});

  final IconData icon;
  final String title;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
      child: Row(
        children: [
          ExcludeSemantics(child: Icon(icon, color: theme.colorScheme.primary)),
          const SizedBox(width: 12),
          Expanded(
            child: Semantics(header: true, child: Text(title, style: theme.textTheme.titleMedium)),
          ),
        ],
      ),
    );
  }
}

class _DetailTile extends StatelessWidget {
  const _DetailTile({required this.label, required this.value, this.copyable = false});

  final String label;
  final String? value;
  final bool copyable;

  @override
  Widget build(BuildContext context) {
    if (value == null) return const SizedBox.shrink();
    return ListTile(
      dense: true,
      title: Text(label),
      subtitle: SelectableText(value!),
      trailing: copyable ? const Icon(Icons.copy, size: 20, semanticLabel: 'Copy') : null,
      onTap: copyable
          ? () async {
              await Clipboard.setData(ClipboardData(text: value!));
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$label copied')));
              }
            }
          : null,
    );
  }
}
