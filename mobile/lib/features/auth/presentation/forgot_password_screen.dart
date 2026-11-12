import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/utils/validators.dart';
import '../../../core/widgets/form_widgets.dart';
import '../../../core/widgets/layout.dart';
import '../data/auth_repository.dart';

class ForgotPasswordScreen extends ConsumerStatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  ConsumerState<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends ConsumerState<ForgotPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _errors = ServerErrors();
  bool _busy = false;
  String? _success;

  @override
  void dispose() {
    _email.dispose();
    _errors.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    _errors.clear();
    setState(() => _success = null);
    if (!(_formKey.currentState?.validate() ?? false)) return;
    setState(() => _busy = true);
    try {
      final message = await ref.read(authRepositoryProvider).forgotPassword(_email.text);
      if (mounted) setState(() => _success = message);
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
      appBar: AppBar(title: const Text('Reset your password')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          ContentWidth(
            maxWidth: 520,
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    'Enter the email address you signed up with and we will send you a link to choose a new password.',
                    style: Theme.of(context).textTheme.bodyLarge,
                  ),
                  const SizedBox(height: 20),
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
                    controller: _email,
                    field: 'email',
                    label: 'Email',
                    errors: _errors,
                    validator: emailValidator,
                    keyboardType: TextInputType.emailAddress,
                    textInputAction: TextInputAction.done,
                    autofillHints: const [AutofillHints.email],
                  ),
                  const SizedBox(height: 20),
                  SubmitButton(label: 'Send reset link', busy: _busy, onPressed: _submit),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
