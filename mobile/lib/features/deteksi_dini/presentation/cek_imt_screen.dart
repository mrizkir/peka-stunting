import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_theme.dart';
import '../../kebutuhan_mu/kebutuhan_mu_config.dart';
import '../domain/bmi_calculator.dart';

class CekImtScreen extends StatefulWidget {
  const CekImtScreen({
    super.key,
    required this.menuSlug,
  });

  final String menuSlug;

  @override
  State<CekImtScreen> createState() => _CekImtScreenState();
}

class _CekImtScreenState extends State<CekImtScreen> {
  final _formKey = GlobalKey<FormState>();
  final _weightController = TextEditingController();
  final _heightController = TextEditingController();

  BmiResult? _result;

  String get _groupLabel =>
      KebutuhanMuConfig.groupTitles[widget.menuSlug] ?? 'Kebutuhanmu';

  @override
  void dispose() {
    _weightController.dispose();
    _heightController.dispose();
    super.dispose();
  }

  void _calculate() {
    if (!(_formKey.currentState?.validate() ?? false)) {
      return;
    }

    final weight = double.parse(_weightController.text.replaceAll(',', '.'));
    final height = double.parse(_heightController.text.replaceAll(',', '.'));

    setState(() {
      _result = BmiCalculator.calculate(
        weightKg: weight,
        heightCm: height,
      );
    });
  }

  void _reset() {
    _weightController.clear();
    _heightController.clear();
    setState(() => _result = null);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Cek IMT'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Text(
            _groupLabel,
            style: TextStyle(
              color: Colors.grey.shade600,
              fontSize: 14,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Hitung Indeks Massa Tubuh (IMT) dari berat badan (kg) dan '
            'tinggi badan (CM).',
            style: TextStyle(color: Colors.grey.shade700, height: 1.4),
          ),
          const SizedBox(height: 20),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    TextFormField(
                      controller: _weightController,
                      keyboardType: const TextInputType.numberWithOptions(
                        decimal: true,
                      ),
                      inputFormatters: [
                        FilteringTextInputFormatter.allow(
                          RegExp(r'[\d.,]'),
                        ),
                      ],
                      decoration: const InputDecoration(
                        labelText: 'Berat badan',
                        hintText: 'Contoh: 52',
                        suffixText: 'kg',
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Masukkan berat badan';
                        }
                        final n = double.tryParse(
                          value.replaceAll(',', '.'),
                        );
                        if (n == null || n <= 0 || n > 300) {
                          return 'Berat tidak valid (1–300 kg)';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _heightController,
                      keyboardType: const TextInputType.numberWithOptions(
                        decimal: true,
                      ),
                      inputFormatters: [
                        FilteringTextInputFormatter.allow(
                          RegExp(r'[\d.,]'),
                        ),
                      ],
                      decoration: const InputDecoration(
                        labelText: 'Tinggi badan (CM)',
                        hintText: 'Contoh: 160',
                        suffixText: 'CM',
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Masukkan tinggi badan (CM)';
                        }
                        final n = double.tryParse(
                          value.replaceAll(',', '.'),
                        );
                        if (n == null || n <= 0 || n > 250) {
                          return 'Tinggi tidak valid (1–250 CM)';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 20),
                    FilledButton(
                      onPressed: _calculate,
                      child: const Text('Hitung IMT'),
                    ),
                    if (_result != null) ...[
                      const SizedBox(height: 12),
                      OutlinedButton(
                        onPressed: _reset,
                        child: const Text('Hitung ulang'),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),
          if (_result != null) ...[
            const SizedBox(height: 16),
            _ResultCard(result: _result!),
          ],
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Text(
                'Hasil ini bersifat skrining awal. Untuk penilaian lebih '
                'lanjut, konsultasikan dengan tenaga kesehatan.',
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.grey.shade600,
                  height: 1.4,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ResultCard extends StatelessWidget {
  const _ResultCard({required this.result});

  final BmiResult result;

  Color get _accentColor {
    switch (result.colorHint) {
      case ColorHint.success:
        return AppTheme.primary;
      case ColorHint.warning:
        return const Color(0xFFD97706);
      case ColorHint.danger:
        return const Color(0xFFDC2626);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Hasil',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
            ),
            const SizedBox(height: 16),
            Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  result.value.toStringAsFixed(1),
                  style: TextStyle(
                    fontSize: 40,
                    fontWeight: FontWeight.bold,
                    color: _accentColor,
                    height: 1,
                  ),
                ),
                const SizedBox(width: 8),
                Padding(
                  padding: const EdgeInsets.only(bottom: 6),
                  child: Text(
                    'kg/m²',
                    style: TextStyle(
                      fontSize: 16,
                      color: Colors.grey.shade600,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: _accentColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                result.categoryLabel,
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  color: _accentColor,
                ),
              ),
            ),
            const SizedBox(height: 12),
            Text(
              result.categoryDescription,
              style: TextStyle(
                color: Colors.grey.shade700,
                height: 1.5,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
