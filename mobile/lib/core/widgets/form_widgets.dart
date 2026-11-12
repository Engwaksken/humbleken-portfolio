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
/// Password fields ([obscureText])
/// get an eye button to show or hide what was typed.
class ApiTextField extends StatefulWidget {
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
  State<ApiTextField> createState() => _ApiTextFieldState();
}

class _ApiTextFieldState extends State<ApiTextField> {
  bool _revealed = false;

  @override
  Widget build(BuildContext context) {
    final w = widget;
    final hidden = w.obscureText && !_revealed;
    return ListenableBuilder(
      listenable: w.errors,
      builder: (context, _) => TextFormField(
        controller: w.controller,
        enabled: w.enabled,
        validator: w.validator,
        keyboardType: w.obscureText && _revealed ? TextInputType.visiblePassword : w.keyboardType,
        textInputAction: w.textInputAction,
        autofillHints: w.autofillHints,
        obscureText: hidden,
        enableSuggestions: !w.obscureText,
        autocorrect: !w.obscureText,
        maxLines: w.obscureText ? 1 : w.maxLines,
        minLines: w.minLines,
        maxLength: w.maxLength,
        forceErrorText: w.errors.of(w.field),
        onChanged: (_) => w.errors.clearField(w.field),
        decoration: InputDecoration(
          labelText: w.isRequired ? '${w.label} (required)' : w.label,
          helperText: w.helperText,
          alignLabelWithHint: (w.maxLines ?? 2) > 1,
          suffixIcon: w.obscureText
              ? IconButton(
                  tooltip: _revealed ? 'Hide password' : 'Show password',
                  onPressed: w.enabled ? () => setState(() => _revealed = !_revealed) : null,
                  icon: Icon(_revealed ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                )
              : null,
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
