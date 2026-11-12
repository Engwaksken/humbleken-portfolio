/// Client-side validators mirroring the Laravel rules. The server remains
/// authoritative; its 422 messages are shown inline as well.
typedef Validator = String? Function(String? value);

final _emailPattern = RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$');

Validator validators(List<Validator> rules) => (value) {
  for (final rule in rules) {
    final error = rule(value);
    if (error != null) return error;
  }
  return null;
};

Validator requiredField(String field) =>
    (value) => (value == null || value.trim().isEmpty) ? 'Enter your $field.' : null;

Validator maxLength(String field, int max) =>
    (value) => (value != null && value.trim().length > max) ? 'The $field must be at most $max characters.' : null;

/// Only applies when there is a value (pair with [requiredField] for mandatory fields).
Validator minLength(String field, int min) => (value) {
  final text = value?.trim() ?? '';
  return (text.isNotEmpty && text.length < min) ? 'The $field must be at least $min characters.' : null;
};

String? emailValidator(String? value) {
  final text = value?.trim() ?? '';
  if (text.isEmpty) return 'Enter your email address.';
  if (!_emailPattern.hasMatch(text)) return 'Enter a valid email address.';
  if (text.length > 255) return 'The email must be at most 255 characters.';
  return null;
}

// Rule sets shared by several forms.
final nameRules = validators([requiredField('name'), maxLength('name', 255)]);
final phoneRules = maxLength('phone number', 50);
