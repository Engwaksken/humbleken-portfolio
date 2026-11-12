/// Small, forgiving helpers for reading API JSON without code generation.
///
/// The API is the source of truth, but the app should degrade gracefully
/// (empty string, empty list, null) rather than crash when a field is
/// missing or has an unexpected type.
library;

typedef Json = Map<String, dynamic>;

String asString(Object? value, [String fallback = '']) {
  if (value == null) return fallback;
  if (value is String) return value;
  return value.toString();
}

/// Returns null for null or blank strings.
String? asStringOrNull(Object? value) {
  if (value == null) return null;
  final text = value is String ? value : value.toString();
  return text.trim().isEmpty ? null : text;
}

int asInt(Object? value, [int fallback = 0]) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value) ?? fallback;
  return fallback;
}

int? asIntOrNull(Object? value) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value);
  return null;
}

bool asBool(Object? value, [bool fallback = false]) {
  if (value is bool) return value;
  if (value is num) return value != 0;
  if (value is String) {
    final v = value.toLowerCase();
    if (v == 'true' || v == '1') return true;
    if (v == 'false' || v == '0') return false;
  }
  return fallback;
}

Json asMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) return value.map((k, v) => MapEntry(k.toString(), v));
  return const {};
}

Json? asMapOrNull(Object? value) {
  if (value is Map) return asMap(value);
  return null;
}

List<T> asList<T>(Object? value, T Function(Json json) parse) {
  if (value is! List) return const [];
  return value.whereType<Map>().map((e) => parse(asMap(e))).toList(growable: false);
}

List<String> asStringList(Object? value) {
  if (value is! List) return const [];
  return value.where((e) => e != null).map((e) => e.toString()).toList(growable: false);
}

DateTime? asDateTime(Object? value) {
  if (value is! String || value.isEmpty) return null;
  return DateTime.tryParse(value)?.toLocal();
}
