import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/widgets/layout.dart';
import '../../pages/data/pages_repository.dart';
import '../../pages/presentation/page_content_view.dart';
import 'contact_details.dart';
import 'enquiry_form.dart';

class ContactScreen extends ConsumerWidget {
  const ContactScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final page = ref.watch(pageContentProvider('contact')).value;
    return Scaffold(
      appBar: AppBar(title: const Text('Contact')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          ContentWidth(
            maxWidth: 720,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (page != null) PageContentView(page: page) else const SectionHeading(title: 'Contact us', level: 1),
                const SizedBox(height: 24),
                const ContactDetails(),
                const SizedBox(height: 24),
                const SectionHeading(title: 'Send an enquiry'),
                const SizedBox(height: 12),
                const EnquiryForm(),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
