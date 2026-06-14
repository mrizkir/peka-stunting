import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../auth/providers/auth_provider.dart';
import '../../kebutuhan_mu/data/kebutuhan_mu_repository.dart';
import '../../kebutuhan_mu/presentation/widgets/kebutuhan_mu_menu_description.dart';
import '../data/nutritional_status_screening_repository.dart';
import '../domain/bmi_calculator.dart';
import '../domain/calculator_anjuran_resolver.dart';
import '../domain/nutritional_status_calculator.dart';
import '../domain/permenkes_z_score.dart';
import '../models/calculator_anjuran_rule.dart';

const _kPeriksaStatusGiziItemSlug = 'periksa-status-gizi';
final _dateFormat = DateFormat('d MMM yyyy', 'id_ID');

final periksaStatusGiziContentProvider =
    StreamProvider.family<KebutuhanMuContentSnapshot?, String>((ref, menuSlug) {
  return ref.read(kebutuhanMuRepositoryProvider).watchContent(
        menuSlug: menuSlug,
        itemSlug: _kPeriksaStatusGiziItemSlug,
      );
});

class PeriksaStatusGiziScreen extends ConsumerStatefulWidget {
  const PeriksaStatusGiziScreen({
    super.key,
    required this.menuSlug,
  });

  final String menuSlug;

  @override
  ConsumerState<PeriksaStatusGiziScreen> createState() =>
      _PeriksaStatusGiziScreenState();
}

class _PeriksaStatusGiziScreenState
    extends ConsumerState<PeriksaStatusGiziScreen> {
  final _formKey = GlobalKey<FormState>();
  final _weightController = TextEditingController();
  final _heightController = TextEditingController();

  DateTime? _birthDate;
  String? _gender;
  NutritionalStatusResult? _result;
  ResolvedAnjuran? _primaryAnjuran;
  bool _isSaving = false;
  final _anjuranResolver = const CalculatorAnjuranResolver();

  @override
  void dispose() {
    _weightController.dispose();
    _heightController.dispose();
    super.dispose();
  }

  ResolvedAnjuran? _resolveAnjuran(double z, String indicator) {
    final content =
        ref.read(periksaStatusGiziContentProvider(widget.menuSlug)).valueOrNull;
    final rules = content?.content.anjuranRules ?? const [];
    if (rules.isEmpty) {
      return null;
    }

    return _anjuranResolver.resolve(
      rules: rules.map(CalculatorAnjuranRule.fromJson).toList(),
      metric: CalculatorAnjuranRule.metricZScore,
      value: z,
      indicator: indicator,
    );
  }

  void _resolveAllAnjurans(NutritionalStatusResult result) {
    final minZ = [
      result.heightForAge.zScore,
      result.weightForAge.zScore,
      result.weightForHeight.zScore,
    ].reduce((a, b) => a < b ? a : b);

    _primaryAnjuran = _resolveAnjuran(minZ, CalculatorAnjuranRule.indicatorPrimary);
  }

  Future<void> _pickBirthDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      locale: const Locale('id', 'ID'),
      helpText: 'Pilih tanggal lahir',
      initialDate: now.subtract(const Duration(days: 365)),
      firstDate: now.subtract(const Duration(days: 365 * 6)),
      lastDate: now,
    );
    if (picked != null) {
      setState(() => _birthDate = picked);
    }
  }

  Future<void> _calculate() async {
    if (!(_formKey.currentState?.validate() ?? false)) {
      return;
    }
    if (_birthDate == null || _gender == null) {
      return;
    }

    final weight = double.parse(_weightController.text.replaceAll(',', '.'));
    final height = double.parse(_heightController.text.replaceAll(',', '.'));
    final nextResult = NutritionalStatusCalculator.calculate(
      NutritionalStatusInput(
        birthDate: _birthDate!,
        gender: _gender!,
        weightKg: weight,
        heightCm: height,
      ),
    );
    if (nextResult == null) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Data tidak valid. Pastikan usia anak 0–60 bulan dan semua '
            'field terisi.',
          ),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() {
      _result = nextResult;
      _resolveAllAnjurans(nextResult);
    });

    final isLoggedIn = ref.read(authStateProvider).valueOrNull != null;
    if (!isLoggedIn) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Hasil ditampilkan. Login untuk menyimpan riwayat status gizi.',
          ),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() => _isSaving = true);
    try {
      await ref.read(nutritionalStatusScreeningRepositoryProvider).submit(
            menuSlug: widget.menuSlug,
            birthDate: _birthDate!,
            gender: _gender!,
            weightKg: weight,
            heightCm: height,
          );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Hasil status gizi berhasil disimpan.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } on ApiException catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error.displayMessage),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Gagal menyimpan hasil status gizi. Periksa koneksi lalu coba lagi.',
          ),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  void _reset() {
    setState(() {
      _birthDate = null;
      _gender = null;
      _weightController.clear();
      _heightController.clear();
      _result = null;
      _primaryAnjuran = null;
      _isSaving = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Periksa Status Gizi'),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(periksaStatusGiziContentProvider(widget.menuSlug));
          await ref.read(periksaStatusGiziContentProvider(widget.menuSlug).future);
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          children: [
          _StatusGiziIntroText(menuSlug: widget.menuSlug),
          const SizedBox(height: 20),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    FormField<String>(
                      validator: (_) =>
                          _birthDate == null ? 'Pilih tanggal lahir' : null,
                      builder: (field) {
                        return InkWell(
                          onTap: () async {
                            await _pickBirthDate();
                            field.didChange(_birthDate?.toIso8601String());
                          },
                          borderRadius: BorderRadius.circular(8),
                          child: InputDecorator(
                            decoration: InputDecoration(
                              labelText: 'Tanggal lahir',
                              errorText: field.errorText,
                            ),
                            child: Text(
                              _birthDate == null
                                  ? 'Pilih tanggal'
                                  : _dateFormat.format(_birthDate!),
                              style: TextStyle(
                                color: _birthDate == null
                                    ? Colors.grey.shade600
                                    : null,
                              ),
                            ),
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 16),
                    DropdownButtonFormField<String>(
                      initialValue: _gender,
                      decoration: const InputDecoration(
                        labelText: 'Jenis kelamin',
                      ),
                      items: const [
                        DropdownMenuItem(value: 'L', child: Text('Laki-laki')),
                        DropdownMenuItem(
                          value: 'P',
                          child: Text('Perempuan'),
                        ),
                      ],
                      onChanged: (value) => setState(() => _gender = value),
                      validator: (value) =>
                          value == null ? 'Pilih jenis kelamin' : null,
                    ),
                    const SizedBox(height: 16),
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
                        hintText: 'Contoh: 10.5',
                        suffixText: 'kg',
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Masukkan berat badan';
                        }
                        final n = double.tryParse(value.replaceAll(',', '.'));
                        if (n == null || n < 0.5 || n > 50) {
                          return 'Berat tidak valid (0,5–50 kg)';
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
                        labelText: 'Tinggi badan',
                        hintText: 'Contoh: 84',
                        suffixText: 'cm',
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Masukkan tinggi badan';
                        }
                        final n = double.tryParse(value.replaceAll(',', '.'));
                        if (n == null || n < 30 || n > 200) {
                          return 'Tinggi tidak valid (30–200 cm)';
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
                          : const Text('Periksa Status Gizi'),
                    ),
                    if (_result != null) ...[
                      const SizedBox(height: 12),
                      OutlinedButton(
                        onPressed: _reset,
                        child: const Text('Periksa ulang'),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),
          if (_result != null) ...[
            const SizedBox(height: 16),
            Text(
              'Hasil keluar di bawah',
              style: TextStyle(
                color: Colors.grey.shade700,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 12),
            _ResultCard(
              result: _result!.heightForAge,
              resolvedAnjuran: _resolveAnjuran(
                _result!.heightForAge.zScore,
                CalculatorAnjuranRule.indicatorHeightForAge,
              ),
            ),
            const SizedBox(height: 12),
            _ResultCard(
              result: _result!.weightForAge,
              resolvedAnjuran: _resolveAnjuran(
                _result!.weightForAge.zScore,
                CalculatorAnjuranRule.indicatorWeightForAge,
              ),
            ),
            const SizedBox(height: 12),
            _ResultCard(
              result: _result!.weightForHeight,
              resolvedAnjuran: _resolveAnjuran(
                _result!.weightForHeight.zScore,
                CalculatorAnjuranRule.indicatorWeightForHeight,
              ),
            ),
            if (_primaryAnjuran != null) ...[
              const SizedBox(height: 16),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Anjuran utama',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.w600,
                            ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        _primaryAnjuran!.anjuran,
                        style: TextStyle(
                          color: Colors.grey.shade700,
                          height: 1.5,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ],
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Text(
                'Perhitungan menggunakan rumus z-score Permenkes No.2 '
                'Tahun 2020. Hasil bersifat skrining — konsultasikan dengan '
                'kader posyandu atau tenaga kesehatan.',
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

class _StatusGiziIntroText extends ConsumerWidget {
  const _StatusGiziIntroText({required this.menuSlug});

  final String menuSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final introStyle = TextStyle(color: Colors.grey.shade700, height: 1.4);
    final contentAsync = ref.watch(periksaStatusGiziContentProvider(menuSlug));

    Widget introFromSnapshot(KebutuhanMuContentSnapshot? snapshot) {
      final content = snapshot?.content;
      final excerpt = content?.excerpt?.trim();
      final body = content?.body?.trim();
      final hasExcerpt = excerpt != null && excerpt.isNotEmpty;
      final hasBody = body != null && body.isNotEmpty;

      if (!hasExcerpt && !hasBody) {
        return Text(
          'Isi tanggal lahir, jenis kelamin, berat badan, dan tinggi badan '
          'anak untuk memeriksa status gizi.',
          style: introStyle,
        );
      }

      return Card(
        clipBehavior: Clip.antiAlias,
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
      );
    }

    return contentAsync.when(
      loading: () => Text(
        'Isi tanggal lahir, jenis kelamin, berat badan, dan tinggi badan '
        'anak untuk memeriksa status gizi.',
        style: introStyle,
      ),
      error: (_, __) => Text(
        'Isi tanggal lahir, jenis kelamin, berat badan, dan tinggi badan '
        'anak untuk memeriksa status gizi.',
        style: introStyle,
      ),
      data: introFromSnapshot,
    );
  }
}

class _ResultCard extends StatelessWidget {
  const _ResultCard({
    required this.result,
    required this.resolvedAnjuran,
  });

  final ZScoreAssessment result;
  final ResolvedAnjuran? resolvedAnjuran;

  String get _categoryLabel =>
      resolvedAnjuran?.label ?? result.categoryLabel;

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
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  flex: 2,
                  child: Text(
                    result.indicatorLabel,
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
                Expanded(
                  flex: 3,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        'Z-score: ${result.zScore.toStringAsFixed(2)}',
                        style: TextStyle(
                          fontWeight: FontWeight.w600,
                          color: _accentColor,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _categoryLabel,
                        textAlign: TextAlign.right,
                        style: TextStyle(color: Colors.grey.shade700),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            if (resolvedAnjuran != null) ...[
              const SizedBox(height: 12),
              Text(
                resolvedAnjuran!.anjuran,
                style: TextStyle(
                  color: Colors.grey.shade700,
                  height: 1.45,
                  fontSize: 13,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
