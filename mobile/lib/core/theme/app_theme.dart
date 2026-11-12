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

/// Brand colours and decoration shared by the hero, banners and card headers.
@immutable
class BrandTokens extends ThemeExtension<BrandTokens> {
  const BrandTokens({required this.primary, required this.deep, required this.cardShadow});

  /// Brand primary (links, accents).
  final Color primary;

  /// Dark brand colour used behind white text (hero, banners).
  final Color deep;

  final List<BoxShadow> cardShadow;

  /// Diagonal deep-to-primary gradient. White text on it meets WCAG AA because
  /// both ends are dark enough (checked when the theme is built).
  LinearGradient get heroGradient {
    var end = Color.lerp(deep, primary, 0.85)!;
    for (var i = 0; i < 10 && contrastRatio(end, Colors.white) < 4.5; i++) {
      end = Color.lerp(end, Colors.black, 0.15)!;
    }
    return LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [deep, end]);
  }

  static BrandTokens of(BuildContext context) =>
      Theme.of(context).extension<BrandTokens>() ??
      const BrandTokens(primary: AppTheme.fallbackPrimary, deep: AppTheme.fallbackSecondary, cardShadow: []);

  @override
  BrandTokens copyWith({Color? primary, Color? deep, List<BoxShadow>? cardShadow}) =>
      BrandTokens(primary: primary ?? this.primary, deep: deep ?? this.deep, cardShadow: cardShadow ?? this.cardShadow);

  @override
  BrandTokens lerp(BrandTokens? other, double t) {
    if (other == null) return this;
    return BrandTokens(
      primary: Color.lerp(primary, other.primary, t)!,
      deep: Color.lerp(deep, other.deep, t)!,
      cardShadow: t < 0.5 ? cardShadow : other.cardShadow,
    );
  }
}

class AppTheme {
  AppTheme._();

  static const fallbackPrimary = Color(0xFF077784);
  static const fallbackSecondary = Color(0xFF14263D);

  static const fontFamily = 'Inter';

  /// Corner radius for cards, images and banners.
  static const radius = 20.0;

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
      surface: Colors.white,
      onSurface: const Color(0xFF0F1B2D),
      onSurfaceVariant: const Color(0xFF4A5568),
      surfaceContainerLowest: Colors.white,
      surfaceContainerLow: const Color(0xFFF7F9FC),
      surfaceContainer: const Color(0xFFF1F4F9),
      surfaceContainerHigh: const Color(0xFFEAEFF5),
      surfaceContainerHighest: const Color(0xFFE2E8F0),
      outline: const Color(0xFFB8C2D0),
      outlineVariant: const Color(0xFFE3E8EF),
    );
    return _build(
      scheme,
      background: const Color(0xFFF4F7FB),
      tokens: BrandTokens(
        primary: usePrimary,
        deep: _deep(useSecondary),
        cardShadow: const [
          BoxShadow(color: Color(0x0F0F1B2D), blurRadius: 24, offset: Offset(0, 8)),
          BoxShadow(color: Color(0x0A0F1B2D), blurRadius: 4, offset: Offset(0, 1)),
        ],
      ),
    );
  }

  /// Dark theme seeded from the brand primary; tonal palettes keep contrast.
  static ThemeData dark({String? primaryHex}) {
    final primary = parseHexColor(primaryHex) ?? fallbackPrimary;
    final scheme = ColorScheme.fromSeed(seedColor: primary, brightness: Brightness.dark);
    return _build(
      scheme,
      background: scheme.surface,
      tokens: BrandTokens(
        primary: scheme.primary,
        deep: Color.lerp(scheme.surfaceContainerHighest, primary, 0.35)!,
        cardShadow: const [],
      ),
    );
  }

  /// The secondary colour, darkened until white text on it reaches 7:1.
  static Color _deep(Color color) {
    var deep = color;
    for (var i = 0; i < 10 && contrastRatio(deep, Colors.white) < 7; i++) {
      deep = Color.lerp(deep, Colors.black, 0.15)!;
    }
    return deep;
  }

  static ThemeData _build(ColorScheme scheme, {required Color background, required BrandTokens tokens}) {
    final base = ThemeData(useMaterial3: true, colorScheme: scheme, fontFamily: fontFamily);
    final text = _textTheme(base.textTheme, scheme);
    final shape = RoundedRectangleBorder(borderRadius: BorderRadius.circular(14));
    const buttonText = TextStyle(fontFamily: fontFamily, fontSize: 15, fontWeight: FontWeight.w600, letterSpacing: 0.1);

    return base.copyWith(
      textTheme: text,
      scaffoldBackgroundColor: background,
      materialTapTargetSize: MaterialTapTargetSize.padded,
      visualDensity: VisualDensity.standard,
      extensions: [tokens],
      splashFactory: InkSparkle.splashFactory,
      appBarTheme: AppBarTheme(
        centerTitle: false,
        backgroundColor: background,
        foregroundColor: scheme.onSurface,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0.5,
        shadowColor: Colors.black26,
        titleTextStyle: text.titleLarge,
      ),
      cardTheme: CardThemeData(
        clipBehavior: Clip.antiAlias,
        elevation: 0,
        margin: EdgeInsets.zero,
        color: scheme.surfaceContainerLowest,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radius),
          side: BorderSide(color: scheme.outlineVariant),
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 68,
        elevation: 0,
        backgroundColor: scheme.surfaceContainerLowest,
        surfaceTintColor: Colors.transparent,
        indicatorColor: scheme.primary.withValues(alpha: 0.12),
        indicatorShape: const StadiumBorder(),
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        iconTheme: WidgetStateProperty.resolveWith(
          (states) => IconThemeData(
            size: 24,
            color: states.contains(WidgetState.selected) ? scheme.primary : scheme.onSurfaceVariant,
          ),
        ),
        labelTextStyle: WidgetStateProperty.resolveWith(
          (states) => text.labelMedium?.copyWith(
            fontWeight: states.contains(WidgetState.selected) ? FontWeight.w700 : FontWeight.w500,
            color: states.contains(WidgetState.selected) ? scheme.primary : scheme.onSurfaceVariant,
          ),
        ),
      ),
      drawerTheme: DrawerThemeData(
        backgroundColor: scheme.surfaceContainerLowest,
        surfaceTintColor: Colors.transparent,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.horizontal(right: Radius.circular(24))),
      ),
      listTileTheme: ListTileThemeData(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        selectedTileColor: scheme.primary.withValues(alpha: 0.1),
        iconColor: scheme.onSurfaceVariant,
      ),
      dividerTheme: DividerThemeData(color: scheme.outlineVariant, thickness: 1, space: 24),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: scheme.surfaceContainerLowest,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: scheme.outline),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: scheme.outlineVariant, width: 1.2),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: scheme.primary, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: scheme.error, width: 1.2),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: scheme.error, width: 2),
        ),
        labelStyle: TextStyle(color: scheme.onSurfaceVariant),
        floatingLabelStyle: TextStyle(color: scheme.primary, fontWeight: FontWeight.w600),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(64, 52),
          padding: const EdgeInsets.symmetric(horizontal: 22),
          shape: shape,
          textStyle: buttonText,
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(minimumSize: const Size(64, 52), shape: shape, textStyle: buttonText),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size(64, 52),
          padding: const EdgeInsets.symmetric(horizontal: 22),
          shape: shape,
          side: BorderSide(color: scheme.outline),
          textStyle: buttonText,
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(minimumSize: const Size(48, 48), shape: shape, textStyle: buttonText),
      ),
      chipTheme: ChipThemeData(
        shape: const StadiumBorder(),
        side: BorderSide(color: scheme.outlineVariant),
        backgroundColor: scheme.surfaceContainerLowest,
        selectedColor: scheme.primary.withValues(alpha: 0.12),
        labelStyle: text.labelLarge,
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
      floatingActionButtonTheme: FloatingActionButtonThemeData(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
      ),
    );
  }

  /// Tighter, bolder headings and comfortable body line height.
  static TextTheme _textTheme(TextTheme base, ColorScheme scheme) {
    TextStyle? heading(TextStyle? s, double size, FontWeight weight, double spacing) => s?.copyWith(
      fontFamily: fontFamily,
      fontSize: size,
      fontWeight: weight,
      letterSpacing: spacing,
      height: 1.2,
      color: scheme.onSurface,
    );
    TextStyle? body(TextStyle? s, double size, {Color? color}) => s?.copyWith(
      fontFamily: fontFamily,
      fontSize: size,
      height: 1.55,
      letterSpacing: 0,
      color: color ?? scheme.onSurfaceVariant,
    );

    return base.copyWith(
      displaySmall: heading(base.displaySmall, 34, FontWeight.w800, -0.8),
      headlineLarge: heading(base.headlineLarge, 30, FontWeight.w800, -0.6),
      headlineMedium: heading(base.headlineMedium, 26, FontWeight.w800, -0.5),
      headlineSmall: heading(base.headlineSmall, 22, FontWeight.w700, -0.3),
      titleLarge: heading(base.titleLarge, 19, FontWeight.w700, -0.2),
      titleMedium: heading(base.titleMedium, 16, FontWeight.w600, -0.1),
      titleSmall: heading(base.titleSmall, 14, FontWeight.w600, 0),
      bodyLarge: body(base.bodyLarge, 16),
      bodyMedium: body(base.bodyMedium, 14.5),
      bodySmall: body(base.bodySmall, 12.5),
      labelLarge: base.labelLarge?.copyWith(fontFamily: fontFamily, fontWeight: FontWeight.w600),
      labelMedium: base.labelMedium?.copyWith(fontFamily: fontFamily, fontWeight: FontWeight.w600),
      labelSmall: base.labelSmall?.copyWith(fontFamily: fontFamily, fontWeight: FontWeight.w600, letterSpacing: 0.6),
    );
  }
}
