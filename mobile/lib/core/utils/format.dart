import 'package:intl/intl.dart';

String formatDate(DateTime? date) => date == null ? '' : DateFormat.yMMMd().format(date);

String formatDateTime(DateTime? date) => date == null ? '' : DateFormat.yMMMd().add_jm().format(date);

/// ISO 8601 with the device's UTC offset, e.g. `2026-11-20T14:30:00+03:00`.
/// Keeps both the wall-clock time the user picked and its absolute instant.
String toIso8601WithOffset(DateTime value) {
  final local = value.toLocal();
  final offset = local.timeZoneOffset;
  final sign = offset.isNegative ? '-' : '+';
  final abs = offset.abs();
  String two(int n) => n.toString().padLeft(2, '0');
  final date = '${local.year.toString().padLeft(4, '0')}-${two(local.month)}-${two(local.day)}';
  final time = '${two(local.hour)}:${two(local.minute)}:${two(local.second)}';
  return '${date}T$time$sign${two(abs.inHours)}:${two(abs.inMinutes % 60)}';
}
