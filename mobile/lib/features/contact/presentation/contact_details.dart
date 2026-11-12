import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/utils/links.dart';
import '../../../core/widgets/layout.dart';
import '../../site/data/site_models.dart';
import '../../site/data/site_repository.dart';

IconData socialIcon(String platform) => switch (platform.toLowerCase()) {
  'linkedin' || 'facebook' || 'x' || 'twitter' || 'instagram' || 'tiktok' => Icons.people_alt_outlined,
  'github' || 'gitlab' => Icons.code,
  'youtube' => Icons.smart_display_outlined,
  'whatsapp' => Icons.chat_outlined,
  _ => Icons.link,
};

/// Contact details from `/site`, each tappable where it makes sense.
class ContactDetails extends ConsumerWidget {
  const ContactDetails({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final site = ref.watch(siteConfigProvider);
    final SiteContact contact = site.contact;
    if (contact.isEmpty && site.socialLinks.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const SectionHeading(title: 'Get in touch'),
        const SizedBox(height: 8),
        Card(
          margin: EdgeInsets.zero,
          child: Column(
            children: [
              if (contact.email != null)
                ListTile(
                  leading: const Icon(Icons.email_outlined),
                  title: const Text('Email'),
                  subtitle: Text(contact.email!),
                  onTap: () => openExternal(context, mailtoUri(contact.email!)),
                ),
              if (contact.phone != null)
                ListTile(
                  leading: const Icon(Icons.phone_outlined),
                  title: const Text('Phone'),
                  subtitle: Text(contact.phone!),
                  onTap: () => openExternal(context, telUri(contact.phone!)),
                ),
              if (contact.whatsapp != null)
                ListTile(
                  leading: const Icon(Icons.chat_outlined),
                  title: const Text('WhatsApp'),
                  subtitle: Text(contact.whatsapp!),
                  onTap: () => openExternal(context, whatsappUri(contact.whatsapp!)),
                ),
              if (contact.businessHours != null)
                ListTile(
                  leading: const Icon(Icons.schedule),
                  title: const Text('Business hours'),
                  subtitle: Text(contact.businessHours!),
                ),
              if (contact.address != null)
                ListTile(
                  leading: const Icon(Icons.place_outlined),
                  title: const Text('Address'),
                  subtitle: Text(contact.address!),
                ),
              for (final link in site.socialLinks)
                ListTile(
                  leading: Icon(socialIcon(link.platform)),
                  title: Text(link.label),
                  subtitle: Text(Uri.tryParse(link.url)?.host ?? link.url),
                  trailing: const Icon(Icons.open_in_new, semanticLabel: 'Opens externally'),
                  onTap: () => openExternalUrl(context, link.url),
                ),
            ],
          ),
        ),
      ],
    );
  }
}
