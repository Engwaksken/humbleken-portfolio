import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/utils/validators.dart';
import '../../../core/widgets/form_widgets.dart';
import '../../services/presentation/service_dropdown.dart';
import '../data/enquiry_repository.dart';

class EnquiryForm extends ConsumerStatefulWidget {
  const EnquiryForm({super.key});

  @override
  ConsumerState<EnquiryForm> createState() => _EnquiryFormState();
}

class _EnquiryFormState extends ConsumerState<EnquiryForm> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _message = TextEditingController();
  final _errors = ServerErrors();
  int? _serviceId;
  bool _busy = false;
  String? _success;

  static final _messageRules = validators([
    requiredField('message'),
    minLength('message', 10),
    maxLength('message', 5000),
  ]);

  @override
  void dispose() {
    for (final c in [_name, _email, _phone, _message]) {
      c.dispose();
    }
    _errors.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    _errors.clear();
    setState(() => _success = null);
    if (!(_formKey.currentState?.validate() ?? false)) return;

    setState(() => _busy = true);
    try {
      final message = await ref
          .read(enquiryRepositoryProvider)
          .submit(
            name: _name.text,
            email: _email.text,
            phone: _phone.text,
            serviceId: _serviceId,
            message: _message.text,
          );
      if (!mounted) return;
      _formKey.currentState?.reset();
      for (final c in [_name, _email, _phone, _message]) {
        c.clear();
      }
      setState(() {
        _serviceId = null;
        _success = message;
      });
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
    return Form(
      key: _formKey,
      child: AutofillGroup(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (_success != null) ...[
              FormStatusMessage(message: _success!, isError: false),
              const SizedBox(height: 16),
            ],
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
              controller: _name,
              field: 'name',
              label: 'Name',
              isRequired: true,
              errors: _errors,
              validator: nameRules,
              autofillHints: const [AutofillHints.name],
            ),
            const SizedBox(height: 16),
            ApiTextField(
              controller: _email,
              field: 'email',
              label: 'Email',
              isRequired: true,
              errors: _errors,
              validator: emailValidator,
              keyboardType: TextInputType.emailAddress,
              autofillHints: const [AutofillHints.email],
            ),
            const SizedBox(height: 16),
            ApiTextField(
              controller: _phone,
              field: 'phone',
              label: 'Phone (optional)',
              errors: _errors,
              validator: phoneRules,
              keyboardType: TextInputType.phone,
              autofillHints: const [AutofillHints.telephoneNumber],
            ),
            const SizedBox(height: 16),
            ListenableBuilder(
              listenable: _errors,
              builder: (context, _) => ServiceDropdown(
                value: _serviceId,
                errorText: _errors.of('service_id'),
                onChanged: (id) {
                  _errors.clearField('service_id');
                  setState(() => _serviceId = id);
                },
              ),
            ),
            const SizedBox(height: 16),
            ApiTextField(
              controller: _message,
              field: 'message',
              label: 'How can I help?',
              isRequired: true,
              errors: _errors,
              validator: _messageRules,
              maxLines: 6,
              minLines: 4,
              keyboardType: TextInputType.multiline,
              textInputAction: TextInputAction.newline,
              helperText: 'At least 10 characters.',
            ),
            const SizedBox(height: 20),
            SubmitButton(label: 'Send enquiry', busy: _busy, onPressed: _submit),
          ],
        ),
      ),
    );
  }
}
