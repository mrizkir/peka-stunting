import 'package:flutter/material.dart';

class AppTheme {
  static const Color primary = Color(0xFF059669);
  static const Color primaryDark = Color(0xFF047857);

  static const Color profileCardBackground = Color(0xFFE8F4FC);
  static const Color kebutuhanMuCardBackground = Color(0xFFF97316);
  static const Color infoAppCardBackground = Color(0xFFD1FAE5);
  static const double homeCardRadius = 22;

  static const LinearGradient appBackgroundGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xFFE1ECC8),
      Color(0xFFF8FAFC),
      Color(0xFFA0C49D),
    ],
    stops: [0.0, 0.5, 1.0],
  );

  static ThemeData light() {
    final colorScheme = ColorScheme.fromSeed(
      seedColor: primary,
      primary: primary,
      secondary: const Color(0xFF0EA5E9),
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: Colors.transparent,
      appBarTheme: const AppBarTheme(
        centerTitle: false,
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: Color(0xFF0F172A),
      ),
      cardTheme: CardThemeData(
        elevation: 0,
        color: Colors.white,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: Color(0xFFE2E8F0)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      ),
    );
  }
}
