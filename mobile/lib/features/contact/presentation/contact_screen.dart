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
      appBar: brandedAppBar(context, title: 'Contact'),
      body: BrandedPageBody(
        maxWidth: 720,
        header: page != null ? PageHeader(page: page) : const GradientHeader(title: 'Contact us'),
        children: [
          if (page != null) PageContentView(page: page),
          const ContactDetails(),
          const SizedBox(height: 28),
          const SectionHeading(title: 'Send an enquiry', subtitle: 'We usually reply within one working day.'),
          const SizedBox(height: 16),
          const SurfaceCard(child: EnquiryForm()),
        ],
      ),
    );
  }
}
