import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/peka_app_bar.dart';
import '../../auth/providers/auth_provider.dart';
import '../../kebutuhan_mu/data/kebutuhan_mu_repository.dart';
import '../../kebutuhan_mu/kebutuhan_mu_config.dart';
import '../../kebutuhan_mu/models/kebutuhan_mu_models.dart';
import '../../kebutuhan_mu/presentation/widgets/kebutuhan_mu_menu_description.dart';
import '../data/breastfeeding_screening_repository.dart';
import '../domain/breastfeeding_calculator_config.dart';
import '../domain/breastfeeding_success_calculator.dart';
import '../domain/bmi_calculator.dart';
import '../domain/calculator_anjuran_resolver.dart';
import '../models/calculator_anjuran_rule.dart';

const _kCekKeberhasilanMenyusuiItemSlug = 'cek-keberhasilan-menyusui';

final cekKeberhasilanMenyusuiContentProvider =
    FutureProvider.family<KebutuhanMuContent?, String>((ref, menuSlug) async {
  try {
    return await ref.read(kebutuhanMuRepositoryProvider).fetchContent(
          menuSlug: menuSlug,
          itemSlug: _kCekKeberhasilanMenyusuiItemSlug,
        );
  } catch (_) {
    return null;
  }
});

final cekKeberhasilanMenyusuiIntroContentProvider =
    StreamProvider.family<KebutuhanMuContentSnapshot?, String>((ref, menuSlug) {
  return ref.read(kebutuhanMuRepositoryProvider).watchContent(
        menuSlug: menuSlug,
        itemSlug: _kCekKeberhasilanMenyusuiItemSlug,
      );
});

class CekKeberhasilanMenyusuiScreen extends ConsumerStatefulWidget {
  const CekKeberhasilanMenyusuiScreen({
    super.key,
    required this.menuSlug,
  });

  final String menuSlug;

  @override
  ConsumerState<CekKeberhasilanMenyusuiScreen> createState() =>
      _CekKeberhasilanMenyusuiScreenState();
}

class _CekKeberhasilanMenyusuiScreenState
    extends ConsumerState<CekKeberhasilanMenyusuiScreen> {
  String get _groupLabel =>
      KebutuhanMuConfig.groupTitles[widget.menuSlug] ?? 'Kebutuhanmu';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: PekaAppBar(
        title: const Text('Cek Keberhasilan Menyusui'),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(cekKeberhasilanMenyusuiContentProvider(widget.menuSlug));
          ref.invalidate(
              cekKeberhasilanMenyusuiIntroContentProvider(widget.menuSlug));
          await ref
              .read(cekKeberhasilanMenyusuiContentProvider(widget.menuSlug).future);
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          children: [
            Text(
              _groupLabel,
              style: TextStyle(
                color: Colors.grey.shade600,
                fontSize: 14,
              ),
            ),
            const SizedBox(height: 8),
            _MenyusuiIntroText(menuSlug: widget.menuSlug),
            const SizedBox(height: 20),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Petunjuk',
                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.w600,
                          ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                    'Jawab setiap pertanyaan berdasarkan kondisi bayi dan pola '
                    'menyusui Anda saat ini. Setiap jawaban "Ya" bernilai 1 poin. '
                    'Skor 8–10 = Menyusui Berhasil; skor di bawah 8 = Perlu Evaluasi '
                    'dan Dukungan Menyusui.',
                      style: TextStyle(
                        color: Colors.grey.shade700,
                        height: 1.5,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            _MenyusuiQuestionnaireCard(menuSlug: widget.menuSlug),
          ],
        ),
      ),
    );
  }
}

class _MenyusuiQuestionnaireCard extends ConsumerStatefulWidget {
  const _MenyusuiQuestionnaireCard({required this.menuSlug});

  final String menuSlug;

  @override
  ConsumerState<_MenyusuiQuestionnaireCard> createState() =>
      _MenyusuiQuestionnaireCardState();
}

class _MenyusuiQuestionnaireCardState
    extends ConsumerState<_MenyusuiQuestionnaireCard> {
  final _formKey = GlobalKey<FormState>();
  List<BreastfeedingScreeningQuestion> _questions =
      BreastfeedingSuccessCalculator.defaultQuestions;
  Map<String, bool?> _answers = {
    for (final q in BreastfeedingSuccessCalculator.defaultQuestions) q.id: null,
  };
  BreastfeedingSuccessResult? _result;
  ResolvedAnjuran? _resolvedAnjuran;
  bool _isSaving = false;
  String? _questionsSignature;
  int _resetGeneration = 0;
  final _anjuranResolver = const CalculatorAnjuranResolver();

  void _applyQuestionnaire({
    required List<BreastfeedingScreeningQuestion> questions,
  }) {
    final signature = questions.map((q) => q.id).join('|');
    if (_questionsSignature == signature) {
      return;
    }

    setState(() {
      _questionsSignature = signature;
      _questions = questions;
      _answers = {for (final q in questions) q.id: null};
      _result = null;
      _resolvedAnjuran = null;
    });
  }

  List<BreastfeedingScreeningQuestion> _resolveQuestionsFromContent(
    KebutuhanMuContent? content,
  ) {
    final config =
        BreastfeedingCalculatorConfig.fromJson(content?.calculatorConfig);
    if (config != null) {
      return config.questions;
    }

    return BreastfeedingSuccessCalculator.defaultQuestions;
  }

  ResolvedAnjuran? _resolveAnjuran(int yesCount) {
    final content = ref
        .read(cekKeberhasilanMenyusuiIntroContentProvider(widget.menuSlug))
        .valueOrNull;
    final rules = content?.content.anjuranRules ?? const [];
    if (rules.isEmpty) {
      return null;
    }

    return _anjuranResolver.resolve(
      rules: rules.map(CalculatorAnjuranRule.fromJson).toList(),
      metric: CalculatorAnjuranRule.metricYesCount,
      value: yesCount.toDouble(),
    );
  }

  Future<void> _calculate() async {
    if (!(_formKey.currentState?.validate() ?? false)) {
      return;
    }

    final answers = {
      for (final entry in _answers.entries) entry.key: entry.value!,
    };

    final result = BreastfeedingSuccessCalculator.calculate(
      questions: _questions,
      answers: answers,
    );

    if (result == null) {
      return;
    }

    setState(() {
      _result = result;
      _resolvedAnjuran = _resolveAnjuran(result.yesCount);
    });

    final isLoggedIn = ref.read(authStateProvider).valueOrNull != null;
    if (!isLoggedIn) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Hasil ditampilkan. Login untuk menyimpan riwayat skrining.',
          ),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() => _isSaving = true);
    try {
      await ref.read(breastfeedingScreeningRepositoryProvider).submit(
            menuSlug: widget.menuSlug,
            answers: answers,
          );
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Jawaban skrining berhasil disimpan.'),
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
          content: Text('Gagal menyimpan jawaban. Periksa koneksi lalu coba lagi.'),
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
    setState(() {
      _answers = {for (final q in _questions) q.id: null};
      _result = null;
      _resolvedAnjuran = null;
      _isSaving = false;
      _resetGeneration++;
    });
  }

  @override
  Widget build(BuildContext context) {
    final contentAsync =
        ref.watch(cekKeberhasilanMenyusuiContentProvider(widget.menuSlug));

    final questions = _resolveQuestionsFromContent(contentAsync.valueOrNull);
    final signature = questions.map((q) => q.id).join('|');
    if (_questionsSignature != signature) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) {
          return;
        }
        _applyQuestionnaire(questions: questions);
      });
    }

    return contentAsync.when(
      loading: () => const Card(
        child: Padding(
          padding: EdgeInsets.all(32),
          child: Center(child: CircularProgressIndicator()),
        ),
      ),
      error: (_, __) => _buildForm(context),
      data: (_) => _buildForm(context),
    );
  }

  Widget _buildForm(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    'Kuesioner skrining',
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                  ),
                  const SizedBox(height: 16),
                  for (var i = 0; i < _questions.length; i++) ...[
                    _QuestionTile(
                      key: ValueKey('${_questions[i].id}-$_resetGeneration'),
                      index: i + 1,
                      question: _questions[i],
                      value: _answers[_questions[i].id],
                      onChanged: (value) {
                        setState(() {
                          _answers[_questions[i].id] = value;
                        });
                      },
                      validator: (value) {
                        if (value == null) {
                          return 'Pilih Ya atau Tidak';
                        }
                        return null;
                      },
                    ),
                    if (i < _questions.length - 1) const SizedBox(height: 16),
                  ],
                  const SizedBox(height: 20),
                  FilledButton(
                    onPressed: _isSaving ? null : _calculate,
                    child: _isSaving
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Text('Klik Hasil'),
                  ),
                  if (_result != null && !_isSaving) ...[
                    const SizedBox(height: 12),
                    OutlinedButton(
                      onPressed: _reset,
                      child: const Text('Isi ulang'),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
        if (_result != null) ...[
          const SizedBox(height: 16),
          _MenyusuiResultCard(
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
              'Hasil ini bersifat skrining awal. Konsultasikan ke tenaga kesehatan '
              'atau konselor laktasi bila ada keluhan menyusui.',
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
    );
  }
}

class _MenyusuiIntroText extends ConsumerWidget {
  const _MenyusuiIntroText({required this.menuSlug});

  final String menuSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final introStyle = TextStyle(
      color: Colors.grey.shade700,
      height: 1.5,
    );

    final contentAsync =
        ref.watch(cekKeberhasilanMenyusuiIntroContentProvider(menuSlug));

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

class _QuestionTile extends StatelessWidget {
  const _QuestionTile({
    super.key,
    required this.index,
    required this.question,
    required this.value,
    required this.onChanged,
    required this.validator,
  });

  final int index;
  final BreastfeedingScreeningQuestion question;
  final bool? value;
  final ValueChanged<bool?> onChanged;
  final FormFieldValidator<bool> validator;

  @override
  Widget build(BuildContext context) {
    return FormField<bool>(
      initialValue: value,
      validator: validator,
      builder: (field) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '$index. ${question.text}',
              style: TextStyle(
                color: Colors.grey.shade800,
                height: 1.4,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 8),
            SegmentedButton<bool>(
              segments: const [
                ButtonSegment(value: true, label: Text('Ya')),
                ButtonSegment(value: false, label: Text('Tidak')),
              ],
              emptySelectionAllowed: true,
              selected: field.value != null ? {field.value!} : {},
              onSelectionChanged: (selection) {
                if (selection.isEmpty) {
                  return;
                }
                final selected = selection.first;
                onChanged(selected);
                field.didChange(selected);
              },
            ),
            if (field.hasError) ...[
              const SizedBox(height: 6),
              Text(
                field.errorText!,
                style: TextStyle(
                  color: Theme.of(context).colorScheme.error,
                  fontSize: 12,
                ),
              ),
            ],
          ],
        );
      },
    );
  }
}

class _MenyusuiResultCard extends StatelessWidget {
  const _MenyusuiResultCard({
    required this.result,
    required this.resolvedAnjuran,
  });

  final BreastfeedingSuccessResult result;
  final ResolvedAnjuran? resolvedAnjuran;

  String get _categoryLabel => resolvedAnjuran?.label ??
      BreastfeedingSuccessCalculator.fallbackCategoryLabel(result.yesCount);

  String get _anjuran => resolvedAnjuran?.anjuran ??
      BreastfeedingSuccessCalculator.fallbackRecommendation(result.yesCount);

  Color get _accentColor {
    final slug = resolvedAnjuran?.slug;
    if (slug != null) {
      switch (slug) {
        case 'normal':
          return AppTheme.primary;
        case 'high_risk':
          return const Color(0xFFDC2626);
        case 'medium_risk':
        case 'low_risk':
          return const Color(0xFFD97706);
        default:
          return const Color(0xFFD97706);
      }
    }

    switch (BreastfeedingSuccessCalculator.fallbackColorHint(result.yesCount)) {
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
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: _accentColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                _categoryLabel,
                style: TextStyle(
                  fontSize: 16,
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
