import 'package:flutter/material.dart';

/// Material equivalents of the Font Awesome names in
/// `App\Support\PageContent::ICONS` (see docs/API.md).
const Map<String, IconData> whyIcons = {
  'fa-comments': Icons.forum_outlined,
  'fa-scale-balanced': Icons.balance,
  'fa-circle-check': Icons.check_circle_outline,
  'fa-shield-halved': Icons.shield_outlined,
  'fa-bolt': Icons.bolt,
  'fa-gears': Icons.settings_outlined,
  'fa-server': Icons.dns_outlined,
  'fa-network-wired': Icons.lan_outlined,
  'fa-laptop-code': Icons.laptop_chromebook_outlined,
  'fa-cloud': Icons.cloud_outlined,
  'fa-lock': Icons.lock_outline,
  'fa-headset': Icons.headset_mic_outlined,
  'fa-chart-line': Icons.show_chart,
  'fa-lightbulb': Icons.lightbulb_outline,
  'fa-clock': Icons.schedule,
  'fa-users': Icons.groups_outlined,
};

const IconData fallbackWhyIcon = Icons.star_outline;

/// Maps a Font Awesome name to a Material icon. Accepts bare names
/// (`fa-bolt`), names without the prefix (`bolt`) and full class strings
/// (`fa-solid fa-bolt`). Unknown names get [fallbackWhyIcon].
IconData iconForFontAwesome(String? name) {
  if (name == null) return fallbackWhyIcon;
  final tokens = name.trim().toLowerCase().split(RegExp(r'\s+'));
  for (final token in tokens.reversed) {
    final key = token.startsWith('fa-') ? token : 'fa-$token';
    final icon = whyIcons[key];
    if (icon != null) return icon;
  }
  return fallbackWhyIcon;
}
