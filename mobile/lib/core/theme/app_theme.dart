import 'dart:math' as math;

import 'package:flutter/material.dart';

/// Parses `#rrggbb`, `rrggbb`, `#rgb` or `#aarrggbb`. Returns null if invalid.
Color? parseHexColor(String? input) {
  if (input == null) return null;
  var hex = input.trim().replaceFirst('#', '');
  if (hex.length == 3) hex = hex.split('').map((c) => '$c$c').join();
  if (hex.length == 6) hex = 'ff$hex';
  if (hex.length != 8) return null;
  final value = int.tryParse(hex, radix: 16);
  return value == null ? null : Color(value);
}

/// WCAG 2.x contrast ratio between two opaque colours (1.0 to 21.0).
double contrastRatio(Color a, Color b) {
  final la = a.computeLuminance();
  final lb = b.computeLuminance();
  return (math.max(la, lb) + 0.05) / (math.min(la, lb) + 0.05);
}

/// Black or white, whichever reads better on [background].
Color readableOn(Color background) =>
    contrastRatio(background, Colors.white) >= contrastRatio(background, Colors.black) ? Colors.white : Colors.black;

class AppTheme {
  AppTheme._();

  static const fallbackPrimary = Color(0xFF077784);
  static const fallbackSecondary = Color(0xFF14263D);

  /// Light theme that uses the brand colours as-is when they reach WCAG AA
  /// (4.5:1) against the surface, otherwise the seeded tonal equivalent.
  static ThemeData light({String? primaryHex, String? secondaryHex}) {
    final primary = parseHexColor(primaryHex) ?? fallbackPrimary;
    final secondary = parseHexColor(secondaryHex) ?? fallbackSecondary;
    final seeded = ColorScheme.fromSeed(seedColor: primary, brightness: Brightness.light);

    final usePrimary = contrastRatio(primary, seeded.surface) >= 4.5 ? primary : seeded.primary;
    final useSecondary = contrastRatio(secondary, seeded.surface) >= 4.5 ? secondary : seeded.secondary;

    final scheme = seeded.copyWith(
      primary: usePrimary,
      onPrimary: readableOn(usePrimary),
      secondary: useSecondary,
      onSecondary: readableOn(useSecondary),
    );
    return _build(scheme);
  }

  /// Dark theme seeded from the brand primary; tonal palettes keep contrast.
  static ThemeData dark({String? primaryHex}) {
    final primary = parseHexColor(primaryHex) ?? fallbackPrimary;
    return _build(ColorScheme.fromSeed(seedColor: primary, brightness: Brightness.dark));
  }

  static ThemeData _build(ColorScheme scheme) {
    return ThemeData(
      colorScheme: scheme,
      useMaterial3: true,
      materialTapTargetSize: MaterialTapTargetSize.padded,
      visualDensity: VisualDensity.standard,
      appBarTheme: const AppBarTheme(centerTitle: false),
      cardTheme: CardThemeData(
        clipBehavior: Clip.antiAlias,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: BorderSide(color: scheme.outlineVariant),
        ),
      ),
      inputDecorationTheme: const InputDecorationTheme(border: OutlineInputBorder()),
      filledButtonTheme: FilledButtonThemeData(style: FilledButton.styleFrom(minimumSize: const Size(64, 48))),
      outlinedButtonTheme: OutlinedButtonThemeData(style: OutlinedButton.styleFrom(minimumSize: const Size(64, 48))),
      textButtonTheme: TextButtonThemeData(style: TextButton.styleFrom(minimumSize: const Size(48, 48))),
    );
  }
}
