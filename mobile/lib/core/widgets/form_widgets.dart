import 'package:flutter/material.dart';

import '../api/api_exception.dart';

/// Tracks server-side (422) field errors for a form. Errors are cleared per
/// field as soon as the user edits it.
class ServerErrors extends ChangeNotifier {
  Map<String, List<String>> _errors = const {};
  String? _message;

  String? message() => _message;

  String? of(String field) {
    final list = _errors[field];
    return (list == null || list.isEmpty) ? null : list.first;
  }

  void apply(ApiException error) {
    _errors = error.fieldErrors;
    _message = error.message;
    notifyListeners();
  }

  void setMessage(String? message) {
    _message = message;
    notifyListeners();
  }

  void clearField(String field) {
    if (!_errors.containsKey(field)) return;
    _errors = Map.of(_errors)..remove(field);
    notifyListeners();
  }

  void clear() {
    _errors = const {};
    _message = null;
    notifyListeners();
  }
}

/// Text field wired to [ServerErrors] for the given API [field] name.
class ApiTextField extends StatelessWidget {
  const ApiTextField({
    super.key,
    required this.controller,
    required this.field,
    required this.label,
    required this.errors,
    this.validator,
    this.keyboardType,
    this.textInputAction = TextInputAction.next,
    this.autofillHints,
    this.obscureText = false,
    this.maxLines = 1,
    this.minLines,
    this.maxLength,
    this.helperText,
    this.isRequired = false,
    this.enabled = true,
  });

  final TextEditingController controller;
  final String field;
  final String label;
  final ServerErrors errors;
  final FormFieldValidator<String>? validator;
  final TextInputType? keyboardType;
  final TextInputAction textInputAction;
  final Iterable<String>? autofillHints;
  final bool obscureText;
  final int? maxLines;
  final int? minLines;
  final int? maxLength;
  final String? helperText;
  final bool isRequired;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: errors,
      builder: (context, _) => TextFormField(
        controller: controller,
        enabled: enabled,
        validator: validator,
        keyboardType: keyboardType,
        textInputAction: textInputAction,
        autofillHints: autofillHints,
        obscureText: obscureText,
        maxLines: obscureText ? 1 : maxLines,
        minLines: minLines,
        maxLength: maxLength,
        forceErrorText: errors.of(field),
        onChanged: (_) => errors.clearField(field),
        decoration: InputDecoration(
          labelText: isRequired ? '$label (required)' : label,
          helperText: helperText,
          alignLabelWithHint: (maxLines ?? 2) > 1,
        ),
      ),
    );
  }
}

/// Form-level error or success message, announced to screen readers.
class FormStatusMessage extends StatelessWidget {
  const FormStatusMessage({super.key, required this.message, this.isError = true});

  final String message;
  final bool isError;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final bg = isError ? scheme.errorContainer : scheme.primaryContainer;
    final fg = isError ? scheme.onErrorContainer : scheme.onPrimaryContainer;
    return Semantics(
      liveRegion: true,
      container: true,
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(8)),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ExcludeSemantics(child: Icon(isError ? Icons.error_outline : Icons.check_circle_outline, color: fg)),
            const SizedBox(width: 12),
            Expanded(
              child: Text(message, style: TextStyle(color: fg)),
            ),
          ],
        ),
      ),
    );
  }
}

/// Submit button with a busy state.
class SubmitButton extends StatelessWidget {
  const SubmitButton({super.key, required this.label, required this.busy, required this.onPressed});

  final String label;
  final bool busy;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return FilledButton(
      onPressed: busy ? null : onPressed,
      child: busy
          ? Semantics(
              label: 'Sending',
              child: const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)),
            )
          : Text(label),
    );
  }
}
