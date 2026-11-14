import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/router/routes.dart';
import '../../../core/utils/validators.dart';
import '../../../core/widgets/form_widgets.dart';
import '../../../core/widgets/layout.dart';
import '../data/auth_repository.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _confirm = TextEditingController();
  final _errors = ServerErrors();
  bool _busy = false;

  @override
  void dispose() {
    for (final c in [_name, _email, _password, _confirm]) {
      c.dispose();
    }
    _errors.dispose();
    super.dispose();
  }

  String? _passwordRule(String? value) {
    if (value == null || value.isEmpty) return 'Enter a password.';
    if (value.length < 8) return 'The password must be at least 8 characters.';
    return null;
  }

  String? _confirmRule(String? value) {
    if (value != _password.text) return 'The passwords do not match.';
    return null;
  }

  Future<void> _submit() async {
    _errors.clear();
    if (!(_formKey.currentState?.validate() ?? false)) return;
    setState(() => _busy = true);
    try {
      await ref
          .read(authControllerProvider.notifier)
          .register(
            name: _name.text,
            email: _email.text,
            password: _password.text,
            passwordConfirmation: _confirm.text,
          );
      TextInput.finishAutofillContext();
      if (mounted) context.go(Routes.account);
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
      appBar: AppBar(title: const Text('Create an account')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          ContentWidth(
            maxWidth: 520,
            child: Form(
              key: _formKey,
              child: AutofillGroup(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(
                      'After signing up you will receive an email with a link to verify your address. '
                      'You can open support tickets once it is verified.',
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                    const SizedBox(height: 20),
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
                      controller: _password,
                      field: 'password',
                      label: 'Password',
                      isRequired: true,
                      errors: _errors,
                      validator: _passwordRule,
                      obscureText: true,
                      helperText: 'At least 8 characters.',
                      autofillHints: const [AutofillHints.newPassword],
                    ),
                    const SizedBox(height: 16),
                    ApiTextField(
                      controller: _confirm,
                      field: 'password_confirmation',
                      label: 'Confirm password',
                      isRequired: true,
                      errors: _errors,
                      validator: _confirmRule,
                      obscureText: true,
                      textInputAction: TextInputAction.done,
                      autofillHints: const [AutofillHints.newPassword],
                    ),
                    const SizedBox(height: 20),
                    SubmitButton(label: 'Create account', busy: _busy, onPressed: _submit),
                    const SizedBox(height: 12),
                    TextButton(
                      onPressed: () => context.push(Routes.privacy),
                      child: const Text('By creating an account you agree to the privacy policy. Read it here.'),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
