import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/router/routes.dart';
import '../../../core/utils/validators.dart';
import '../../../core/widgets/form_widgets.dart';
import '../../../core/widgets/layout.dart';
import '../data/tickets_repository.dart';

class NewTicketScreen extends ConsumerStatefulWidget {
  const NewTicketScreen({super.key});

  @override
  ConsumerState<NewTicketScreen> createState() => _NewTicketScreenState();
}

class _NewTicketScreenState extends ConsumerState<NewTicketScreen> {
  final _formKey = GlobalKey<FormState>();
  final _subject = TextEditingController();
  final _body = TextEditingController();
  final _errors = ServerErrors();
  bool _busy = false;

  static final _subjectRules = validators([
    (v) => (v == null || v.trim().isEmpty) ? 'Enter a subject.' : null,
    maxLength('subject', 255),
  ]);
  static final _bodyRules = validators([
    (v) => (v == null || v.trim().isEmpty) ? 'Describe the problem.' : null,
    minLength('description', 10),
    maxLength('description', 10000),
  ]);

  @override
  void dispose() {
    _subject.dispose();
    _body.dispose();
    _errors.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    _errors.clear();
    if (!(_formKey.currentState?.validate() ?? false)) return;
    setState(() => _busy = true);
    try {
      final ticket = await ref.read(ticketsRepositoryProvider).create(subject: _subject.text, body: _body.text);
      ref.invalidate(ticketsProvider);
      if (mounted) context.pushReplacement(Routes.ticket(ticket.id));
    } on ApiException catch (e) {
      _errors.apply(e);
    } catch (e) {
      _errors.setMessage(ApiException.messageOf(e));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('New support ticket')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          ContentWidth(
            maxWidth: 720,
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  ListenableBuilder(
                    listenable: _errors,
                    builder: (context, _) => _errors.message() == null
                        ? const SizedBox.shrink()
                        : Padding(
                            padding: const EdgeInsets.only(bottom: 16),
                            child: FormStatusMessage(message: _errors.message()!),
                          ),
                  ),
                  ApiTextField(
                    controller: _subject,
                    field: 'subject',
                    label: 'Subject',
                    isRequired: true,
                    errors: _errors,
                    validator: _subjectRules,
                  ),
                  const SizedBox(height: 16),
                  ApiTextField(
                    controller: _body,
                    field: 'body',
                    label: 'Describe the problem',
                    isRequired: true,
                    errors: _errors,
                    validator: _bodyRules,
                    minLines: 6,
                    maxLines: 12,
                    keyboardType: TextInputType.multiline,
                    textInputAction: TextInputAction.newline,
                    helperText: 'Include what you tried and any error messages. At least 10 characters.',
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Need to share a file? Add it from the website after creating the ticket.',
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                  const SizedBox(height: 20),
                  SubmitButton(label: 'Create ticket', busy: _busy, onPressed: _submit),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
