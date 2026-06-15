import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/peka_app_bar.dart';
import '../../auth/providers/auth_provider.dart';
import '../../kebutuhan_mu/data/kebutuhan_mu_repository.dart';
import '../../kebutuhan_mu/presentation/widgets/kebutuhan_mu_menu_description.dart';
import '../data/bmi_screening_repository.dart';
import '../domain/bmi_calculator.dart';
import '../domain/calculator_anjuran_resolver.dart';
import '../models/calculator_anjuran_rule.dart';

const _kCekImtItemSlug = 'cek-imt';

final cekImtContentProvider =
    StreamProvider.family<KebutuhanMuContentSnapshot?, String>((ref, menuSlug) {
  return ref.read(kebutuhanMuRepositoryProvider).watchContent(
        menuSlug: menuSlug,
        itemSlug: _kCekImtItemSlug,
      );
});

class CekImtScreen extends ConsumerStatefulWidget {
  const CekImtScreen({
    super.key,
    required this.menuSlug,
  });

  final String menuSlug;

  @override
  ConsumerState<CekImtScreen> createState() => _CekImtScreenState();
}

class _CekImtScreenState extends ConsumerState<CekImtScreen> {
  final _formKey = GlobalKey<FormState>();
  final _weightController = TextEditingController();
  final _heightController = TextEditingController();
  final _anjuranResolver = const CalculatorAnjuranResolver();

  BmiResult? _result;
  ResolvedAnjuran? _resolvedAnjuran;
  bool _isSaving = false;

  @override
  void dispose() {
    _weightController.dispose();
    _heightController.dispose();
    super.dispose();
  }

  ResolvedAnjuran? _resolveAnjuran(double bmi) {
    final content = ref.read(cekImtContentProvider(widget.menuSlug)).valueOrNull;
    final rules = content?.content.anjuranRules ?? const [];
    if (rules.isEmpty) {
      return null;
    }

    return _anjuranResolver.resolve(
      rules: rules.map(CalculatorAnjuranRule.fromJson).toList(),
      metric: CalculatorAnjuranRule.metricBmi,
      value: bmi,
    );
  }

  Future<void> _calculate() async {
    if (!(_formKey.currentState?.validate() ?? false)) {
      return;
    }

    final weight = double.parse(_weightController.text.replaceAll(',', '.'));
    final height = double.parse(_heightController.text.replaceAll(',', '.'));
    final nextResult = BmiCalculator.calculate(
      weightKg: weight,
      heightCm: height,
    );
    if (nextResult == null) {
      return;
    }

    setState(() {
      _result = nextResult;
      _resolvedAnjuran = _resolveAnjuran(nextResult.value);
    });

    final isLoggedIn = ref.read(authStateProvider).valueOrNull != null;
    if (!isLoggedIn) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Hasil ditampilkan. Login untuk menyimpan riwayat IMT.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() => _isSaving = true);
    try {
      await ref.read(bmiScreeningRepositoryProvider).submit(
            menuSlug: widget.menuSlug,
            weightKg: weight,
            heightCm: height,
          );
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Hasil IMT berhasil disimpan.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } on ApiException catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error.displayMessage),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } catch (_) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Gagal menyimpan hasil IMT. Periksa koneksi lalu coba lagi.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isSaving = false);
      }
    }
  }

  void _reset() {
    _weightController.clear();
    _heightController.clear();
    setState(() {
      _result = null;
      _resolvedAnjuran = null;
      _isSaving = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: PekaAppBar(
        title: const Text('Cek IMT'),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(cekImtContentProvider(widget.menuSlug));
          await ref.read(cekImtContentProvider(widget.menuSlug).future);
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          children: [
          const SizedBox(height: 4),
          _ImtIntroText(menuSlug: widget.menuSlug),
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
                      onPressed: _isSaving ? null : _calculate,
                      child: _isSaving
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Text('Hitung IMT'),
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
            _ResultCard(
              result: _result!,
              resolvedAnjuran: _resolvedAnjuran,
            ),
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
      ),
    );
  }
}

class _ImtIntroText extends ConsumerWidget {
  const _ImtIntroText({required this.menuSlug});

  final String menuSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final introStyle = TextStyle(
      color: Colors.grey.shade700,
      height: 1.4,
    );

    final contentAsync = ref.watch(cekImtContentProvider(menuSlug));

    Widget introFromSnapshot(KebutuhanMuContentSnapshot? snapshot) {
      final content = snapshot?.content;
      final excerpt = content?.excerpt?.trim();
      final body = content?.body?.trim();
      final hasExcerpt = excerpt != null && excerpt.isNotEmpty;
      final hasBody = body != null && body.isNotEmpty;

      if (!hasExcerpt && !hasBody) {
        return const SizedBox.shrink();
      }

      return Card(
        clipBehavior: Clip.antiAlias,
        child: Padding(
          padding: EdgeInsets.zero,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (hasExcerpt)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 12,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    border: Border(
                      bottom: BorderSide(color: Colors.grey.shade300),
                    ),
                  ),
                  child: Text(excerpt, style: introStyle),
                ),
              if (hasBody)
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: KebutuhanMuMenuDescription(description: body),
                ),
            ],
          ),
        ),
      );
    }

    return contentAsync.when(
      loading: () => const SizedBox.shrink(),
      error: (_, __) => const SizedBox.shrink(),
      data: introFromSnapshot,
    );
  }
}

class _ResultCard extends StatelessWidget {
  const _ResultCard({
    required this.result,
    required this.resolvedAnjuran,
  });

  final BmiResult result;
  final ResolvedAnjuran? resolvedAnjuran;

  String get _categoryLabel =>
      resolvedAnjuran?.label ?? result.categoryLabel;

  String get _anjuran =>
      resolvedAnjuran?.anjuran ?? result.categoryDescription;

  ColorHint get _colorHint {
    final slug = resolvedAnjuran?.slug;
    if (slug == null) {
      return result.colorHint;
    }

    switch (slug) {
      case 'normal':
        return ColorHint.success;
      case 'obese':
        return ColorHint.danger;
      default:
        return ColorHint.warning;
    }
  }

  Color get _accentColor {
    switch (_colorHint) {
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
                _categoryLabel,
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  color: _accentColor,
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Anjuran',
              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              _anjuran,
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
