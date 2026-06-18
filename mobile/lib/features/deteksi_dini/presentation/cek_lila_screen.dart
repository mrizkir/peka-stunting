import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/peka_app_bar.dart';
import '../../auth/providers/auth_provider.dart';
import '../../kebutuhan_mu/data/kebutuhan_mu_repository.dart';
import '../../kebutuhan_mu/kebutuhan_mu_config.dart';
import '../../kebutuhan_mu/presentation/widgets/kebutuhan_mu_menu_description.dart';
import '../data/lila_screening_repository.dart';
import '../domain/bmi_calculator.dart';
import '../domain/calculator_anjuran_resolver.dart';
import '../domain/lila_calculator.dart';
import '../models/calculator_anjuran_rule.dart';

const _kCekLilaItemSlug = 'cek-lila';

final cekLilaContentProvider =
    StreamProvider.family<KebutuhanMuContentSnapshot?, String>((ref, menuSlug) {
  return ref.read(kebutuhanMuRepositoryProvider).watchContent(
        menuSlug: menuSlug,
        itemSlug: _kCekLilaItemSlug,
      );
});

const _measurementSteps = [
  'Sediakan pita ukur (meteran kain/plastik yang fleksibel) atau pakai '
      'pita LILA khusus dari posyandu/puskesmas.',
  'Tentukan lengan yang diukur — gunakan lengan kiri (standar '
      'pengukuran kesehatan).',
  'Tentukan titik tengah lengan: tekuk siku membentuk sudut 90°, cari '
      'ujung tulang bahu (atas) dan ujung siku (bawah), ukur jarak '
      'antara keduanya, lalu ambil titik tengahnya.',
  'Luruskan lengan: setelah titik tengah ditemukan, luruskan kembali '
      'lengan dan rileks.',
  'Lingkarkan pita ukur tepat di titik tengah. Pastikan tidak terlalu '
      'kencang (menekan kulit), tidak terlalu longgar, dan pita sejajar '
      '(tidak miring).',
];

class CekLilaScreen extends ConsumerStatefulWidget {
  const CekLilaScreen({
    super.key,
    required this.menuSlug,
  });

  final String menuSlug;

  @override
  ConsumerState<CekLilaScreen> createState() => _CekLilaScreenState();
}

class _CekLilaScreenState extends ConsumerState<CekLilaScreen> {
  final _formKey = GlobalKey<FormState>();
  final _ageController = TextEditingController();
  final _lilaController = TextEditingController();

  LilaResult? _result;
  ResolvedAnjuran? _resolvedAnjuran;
  bool _isSaving = false;
  final _anjuranResolver = const CalculatorAnjuranResolver();

  @override
  void dispose() {
    _ageController.dispose();
    _lilaController.dispose();
    super.dispose();
  }

  ResolvedAnjuran? _resolveAnjuran(int ageYears, double lilaCm) {
    final content = ref.read(cekLilaContentProvider(widget.menuSlug)).valueOrNull;
    final rules = content?.content.anjuranRules ?? const [];
    if (rules.isEmpty) {
      return null;
    }

    final indicator = LilaAgeBandHelper.usesAgeBands(widget.menuSlug)
        ? LilaAgeBandHelper.indicatorForAge(ageYears)
        : null;

    return _anjuranResolver.resolve(
      rules: rules.map(CalculatorAnjuranRule.fromJson).toList(),
      metric: CalculatorAnjuranRule.metricLilaCm,
      value: lilaCm,
      indicator: indicator,
    );
  }

  bool get _usesAgeBands =>
      LilaAgeBandHelper.usesAgeBands(widget.menuSlug);

  String? _validateAge(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Masukkan usia';
    }
    final n = int.tryParse(value.trim());
    if (n == null || n <= 0 || n > 120) {
      return 'Usia tidak valid (1–120 tahun)';
    }
    if (_usesAgeBands && n < LilaAgeBandHelper.minRemajaPutriAgeYears) {
      return 'Usia minimal ${LilaAgeBandHelper.minRemajaPutriAgeYears} tahun '
          'untuk skrining remaja putri';
    }
    return null;
  }

  Future<void> _calculate() async {
    if (!(_formKey.currentState?.validate() ?? false)) {
      return;
    }

    final age = int.parse(_ageController.text.trim());
    final lila = double.parse(_lilaController.text.replaceAll(',', '.'));
    final nextResult = LilaCalculator.calculate(
      circumferenceCm: lila,
      menuSlug: widget.menuSlug,
      ageYears: age,
    );
    if (nextResult == null) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            _usesAgeBands
                ? 'Usia di luar rentang remaja putri (minimal '
                    '${LilaAgeBandHelper.minRemajaPutriAgeYears} tahun).'
                : 'Ukuran LILA tidak valid.',
          ),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() {
      _result = nextResult;
      _resolvedAnjuran = _resolveAnjuran(age, nextResult.valueCm);
    });

    final isLoggedIn = ref.read(authStateProvider).valueOrNull != null;
    if (!isLoggedIn) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Hasil ditampilkan. Login untuk menyimpan riwayat LILA.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() => _isSaving = true);
    try {
      await ref.read(lilaScreeningRepositoryProvider).submit(
            menuSlug: widget.menuSlug,
            ageYears: age,
            lilaCm: lila,
          );
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Hasil LILA berhasil disimpan.'),
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
          content: Text('Gagal menyimpan hasil LILA. Periksa koneksi lalu coba lagi.'),
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
    _ageController.clear();
    _lilaController.clear();
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
        logoAssetPath: KebutuhanMuConfig.menuLogoAsset(widget.menuSlug),
        title: const Text('Cek LILA'),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(cekLilaContentProvider(widget.menuSlug));
          await ref.read(cekLilaContentProvider(widget.menuSlug).future);
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          children: [
          const SizedBox(height: 8),
          _LilaIntroText(menuSlug: widget.menuSlug),
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Bagaimana cara mengukur LILA?',
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                  ),
                  const SizedBox(height: 12),
                  for (var i = 0; i < _measurementSteps.length; i++) ...[
                    _StepRow(index: i + 1, text: _measurementSteps[i]),
                    if (i < _measurementSteps.length - 1)
                      const SizedBox(height: 10),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(
                      'Hasil pengukuran',
                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.w600,
                          ),
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _ageController,
                      keyboardType: TextInputType.number,
                      inputFormatters: [
                        FilteringTextInputFormatter.digitsOnly,
                      ],
                      decoration: const InputDecoration(
                        labelText: 'Usia (Tahun)',
                        hintText: 'Contoh: 16',
                        suffixText: 'tahun',
                      ),
                      validator: _validateAge,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _lilaController,
                      keyboardType: const TextInputType.numberWithOptions(
                        decimal: true,
                      ),
                      inputFormatters: [
                        FilteringTextInputFormatter.allow(
                          RegExp(r'[\d.,]'),
                        ),
                      ],
                      decoration: const InputDecoration(
                        labelText: 'LILA (cm)',
                        hintText: 'Contoh: 24,5',
                        suffixText: 'cm',
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Masukkan ukuran LILA';
                        }
                        final n = double.tryParse(
                          value.replaceAll(',', '.'),
                        );
                        if (n == null || n <= 0 || n > 60) {
                          return 'Ukuran tidak valid (1–60 cm)';
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
                          : const Text('Klik Hasil'),
                    ),
                    if (_result != null && !_isSaving) ...[
                      const SizedBox(height: 12),
                      OutlinedButton(
                        onPressed: _reset,
                        child: const Text('Ukur ulang'),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),
          if (_result != null) ...[
            const SizedBox(height: 16),
            _LilaResultCard(
              result: _result!,
              resolvedAnjuran: _resolvedAnjuran,
            ),
          ],
          const SizedBox(height: 16),
          Card(
            color: const Color(0xFFFCE7F3),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Text(
                _usesAgeBands
                    ? 'Interpretasi remaja putri: usia 10–14 tahun (normal ≥ 18,5 cm), '
                        '15–17 tahun (normal ≥ 22 cm), > 17 tahun (normal ≥ 23,5 cm). '
                        'Di bawah ambang masing-masing berisiko KEK. Hasil ini '
                        'bersifat skrining awal — konsultasikan dengan tenaga '
                        'kesehatan untuk penilaian lebih lanjut.'
                    : 'Interpretasi: LILA < ${LilaCalculator.normalMinimumCm} cm '
                        'berisiko KEK; LILA ≥ ${LilaCalculator.normalMinimumCm} cm '
                        'status gizi relatif normal. Hasil ini bersifat skrining awal '
                        '— konsultasikan dengan tenaga kesehatan untuk penilaian '
                        'lebih lanjut.',
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.grey.shade600,
                  fontWeight: FontWeight.bold,
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

class _LilaIntroText extends ConsumerWidget {
  const _LilaIntroText({required this.menuSlug});

  final String menuSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final introStyle = TextStyle(
      color: Colors.grey.shade700,
      height: 1.5,
    );

    final contentAsync = ref.watch(cekLilaContentProvider(menuSlug));

    Widget? introFromSnapshot(KebutuhanMuContentSnapshot? snapshot) {
      final content = snapshot?.content;
      final excerpt = content?.excerpt?.trim();
      final body = content?.body?.trim();
      final hasExcerpt = excerpt != null && excerpt.isNotEmpty;
      final hasBody = body != null && body.isNotEmpty;

      if (!hasExcerpt && !hasBody) {
        return null;
      }

      return Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Deskripsi',
                style: Theme.of(context).textTheme.titleSmall?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
              ),
              const SizedBox(height: 8),
              if (hasExcerpt) Text(excerpt, style: introStyle),
              if (hasExcerpt && hasBody) const SizedBox(height: 8),
              if (hasBody)
                KebutuhanMuMenuDescription(description: body),
            ],
          ),
        ),
      );
    }

    return contentAsync.when(
      loading: () => const SizedBox.shrink(),
      error: (_, __) => const SizedBox.shrink(),
      data: (snapshot) => introFromSnapshot(snapshot) ?? const SizedBox.shrink(),
    );
  }
}

class _StepRow extends StatelessWidget {
  const _StepRow({required this.index, required this.text});

  final int index;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 24,
          child: Text(
            '$index.',
            style: TextStyle(
              fontWeight: FontWeight.w600,
              color: Colors.grey.shade700,
              height: 1.5,
            ),
          ),
        ),
        Expanded(
          child: Text(
            text,
            style: TextStyle(
              color: Colors.grey.shade700,
              height: 1.5,
            ),
          ),
        ),
      ],
    );
  }
}

class _LilaResultCard extends StatelessWidget {
  const _LilaResultCard({
    required this.result,
    required this.resolvedAnjuran,
  });

  final LilaResult result;
  final ResolvedAnjuran? resolvedAnjuran;

  String get _categoryLabel =>
      resolvedAnjuran?.label ?? result.categoryLabel;

  String get _anjuran =>
      resolvedAnjuran?.anjuran ?? result.recommendation;

  Color get _accentColor {
    final slug = resolvedAnjuran?.slug;
    if (slug != null) {
      switch (slug) {
        case 'normal':
          return AppTheme.primary;
        case 'at_risk':
          return const Color(0xFFDC2626);
        default:
          return const Color(0xFFD97706);
      }
    }

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
            const SizedBox(height: 12),
            Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  result.valueCm.toStringAsFixed(1),
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
                    'cm',
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
            const SizedBox(height: 20),
            Text(
              'Anjuran',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              _anjuran,
              textAlign: TextAlign.justify,
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
