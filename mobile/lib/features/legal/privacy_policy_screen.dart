import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/api/api_client.dart';
import '../../core/utils/json.dart';
import '../../core/utils/links.dart';
import '../../core/widgets/layout.dart';
import '../../core/widgets/markdown_view.dart';
import '../../core/widgets/state_views.dart';

class PrivacyPolicy {
  const PrivacyPolicy({required this.content, this.updatedAt, this.url});

  final String content;
  final DateTime? updatedAt;
  final String? url;

  factory PrivacyPolicy.fromJson(Json json) => PrivacyPolicy(
    content: asString(json['content']),
    updatedAt: asDateTime(json['updated_at']),
    url: asStringOrNull(json['url']),
  );
}

final privacyPolicyProvider = FutureProvider<PrivacyPolicy>((ref) async {
  final json = await ref.watch(apiClientProvider).get('privacy-policy');
  return PrivacyPolicy.fromJson(asMap(json['data']));
});

/// The site's privacy policy, as required for the Play Store listing.
class PrivacyPolicyScreen extends ConsumerWidget {
  const PrivacyPolicyScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final policy = ref.watch(privacyPolicyProvider);
    return Scaffold(
      appBar: brandedAppBar(context, title: 'Privacy policy'),
      body: switch (policy) {
        AsyncValue(:final value?) => BrandedPageBody(
          maxWidth: 720,
          onRefresh: () async {
            try {
              ref.invalidate(privacyPolicyProvider);
              await ref.read(privacyPolicyProvider.future);
            } catch (_) {}
          },
          header: GradientHeader(
            eyebrow: 'Privacy',
            title: 'Privacy policy',
            intro: value.updatedAt == null ? null : 'Last updated ${_formatDate(value.updatedAt!)}.',
          ),
          children: [
            MarkdownView(data: value.content),
            if (value.url != null) ...[
              const SizedBox(height: 24),
              Align(
                alignment: Alignment.centerLeft,
                child: TextButton.icon(
                  onPressed: () => openExternalUrl(context, value.url!),
                  icon: const Icon(Icons.open_in_new, size: 18),
                  label: const Text('Open on the website'),
                ),
              ),
            ],
          ],
        ),
        AsyncValue(:final error?) => ErrorView(error: error, onRetry: () => ref.invalidate(privacyPolicyProvider)),
        _ => const LoadingView(label: 'Loading the privacy policy'),
      },
    );
  }

  static String _formatDate(DateTime date) {
    const months = [
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December',
    ];
    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }
}
