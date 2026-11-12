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

class SignInView extends ConsumerStatefulWidget {
  const SignInView({super.key});

  @override
  ConsumerState<SignInView> createState() => _SignInViewState();
}

class _SignInViewState extends ConsumerState<SignInView> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _errors = ServerErrors();
  bool _busy = false;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    _errors.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    _errors.clear();
    if (!(_formKey.currentState?.validate() ?? false)) return;
    setState(() => _busy = true);
    try {
      await ref.read(authControllerProvider.notifier).signIn(email: _email.text, password: _password.text);
      TextInput.finishAutofillContext();
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
    final theme = Theme.of(context);
    return ListView(
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
                  const SectionHeading(eyebrow: 'Account', title: 'Sign in', level: 1),
                  const SizedBox(height: 8),
                  Text('Sign in to view and reply to your support tickets.', style: theme.textTheme.bodyLarge),
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
                    controller: _email,
                    field: 'email',
                    label: 'Email',
                    errors: _errors,
                    validator: emailValidator,
                    keyboardType: TextInputType.emailAddress,
                    autofillHints: const [AutofillHints.email, AutofillHints.username],
                  ),
                  const SizedBox(height: 16),
                  ApiTextField(
                    controller: _password,
                    field: 'password',
                    label: 'Password',
                    errors: _errors,
                    validator: requiredField('password'),
                    obscureText: true,
                    textInputAction: TextInputAction.done,
                    autofillHints: const [AutofillHints.password],
                  ),
                  const SizedBox(height: 20),
                  SubmitButton(label: 'Sign in', busy: _busy, onPressed: _submit),
                  const SizedBox(height: 8),
                  Align(
                    alignment: Alignment.centerLeft,
                    child: TextButton(
                      onPressed: () => context.push(Routes.forgotPassword),
                      child: const Text('Forgot your password?'),
                    ),
                  ),
                  const Divider(height: 32),
                  Text('New here?', style: theme.textTheme.titleMedium),
                  const SizedBox(height: 8),
                  OutlinedButton(
                    onPressed: () => context.push(Routes.register),
                    child: const Text('Create an account'),
                  ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }
}
