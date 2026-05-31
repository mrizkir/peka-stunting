import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../auth/providers/auth_provider.dart';
import '../../kebutuhan_mu/data/kebutuhan_mu_repository.dart';
import '../../kebutuhan_mu/kebutuhan_mu_config.dart';
import '../../kebutuhan_mu/models/kebutuhan_mu_models.dart';
import '../../kebutuhan_mu/presentation/widgets/kebutuhan_mu_menu_description.dart';
import '../data/anemia_screening_repository.dart';
import '../domain/anemia_calculator_config.dart';
import '../domain/anemia_risk_calculator.dart';
import '../domain/bmi_calculator.dart';

const _kCekRisikoAnemiaItemSlug = 'cek-risiko-anemia';

final cekRisikoAnemiaContentProvider =
    FutureProvider.family<KebutuhanMuContent?, String>((ref, menuSlug) async {
  try {
    return await ref.read(kebutuhanMuRepositoryProvider).fetchContent(
          menuSlug: menuSlug,
          itemSlug: _kCekRisikoAnemiaItemSlug,
        );
  } catch (_) {
    return null;
  }
});

final cekRisikoAnemiaIntroContentProvider =
    StreamProvider.family<KebutuhanMuContentSnapshot?, String>((ref, menuSlug) {
  return ref.read(kebutuhanMuRepositoryProvider).watchContent(
        menuSlug: menuSlug,
        itemSlug: _kCekRisikoAnemiaItemSlug,
      );
});

class CekRisikoAnemiaScreen extends ConsumerStatefulWidget {
  const CekRisikoAnemiaScreen({
    super.key,
    required this.menuSlug,
  });

  final String menuSlug;

  @override
  ConsumerState<CekRisikoAnemiaScreen> createState() =>
      _CekRisikoAnemiaScreenState();
}

class _CekRisikoAnemiaScreenState extends ConsumerState<CekRisikoAnemiaScreen> {
  String get _groupLabel =>
      KebutuhanMuConfig.groupTitles[widget.menuSlug] ?? 'Kebutuhanmu';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Cek Risiko Anemia'),
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
          const SizedBox(height: 8),
          _AnemiaIntroText(menuSlug: widget.menuSlug),
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
                    'Jawab setiap pertanyaan dengan jujur berdasarkan kondisi '
                    'Anda dalam 1–2 bulan terakhir. Skrining ini tidak '
                    'menggantikan pemeriksaan Hb di fasilitas kesehatan.',
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
          _AnemiaQuestionnaireCard(menuSlug: widget.menuSlug),
        ],
      ),
    );
  }
}

class _AnemiaQuestionnaireCard extends ConsumerStatefulWidget {
  const _AnemiaQuestionnaireCard({required this.menuSlug});

  final String menuSlug;

  @override
  ConsumerState<_AnemiaQuestionnaireCard> createState() =>
      _AnemiaQuestionnaireCardState();
}

class _AnemiaQuestionnaireCardState
    extends ConsumerState<_AnemiaQuestionnaireCard> {
  final _formKey = GlobalKey<FormState>();
  List<AnemiaScreeningQuestion> _questions =
      AnemiaRiskCalculator.defaultQuestions;
  int _riskYesThreshold = AnemiaRiskCalculator.defaultRiskYesThreshold;
  Map<String, bool?> _answers = {
    for (final q in AnemiaRiskCalculator.defaultQuestions) q.id: null,
  };
  AnemiaRiskResult? _result;
  bool _isSaving = false;
  String? _questionsSignature;

  void _applyQuestionnaire({
    required List<AnemiaScreeningQuestion> questions,
    required int riskYesThreshold,
  }) {
    final signature = questions.map((q) => q.id).join('|');
    if (_questionsSignature == signature) {
      return;
    }

    setState(() {
      _questionsSignature = signature;
      _questions = questions;
      _riskYesThreshold = riskYesThreshold;
      _answers = {for (final q in questions) q.id: null};
      _result = null;
    });
  }

  ({List<AnemiaScreeningQuestion> questions, int threshold}) _resolveFromContent(
    KebutuhanMuContent? content,
  ) {
    final config = AnemiaCalculatorConfig.fromJson(content?.calculatorConfig);
    if (config != null) {
      return (questions: config.questions, threshold: config.riskYesThreshold);
    }

    return (
      questions: AnemiaRiskCalculator.defaultQuestions,
      threshold: AnemiaRiskCalculator.defaultRiskYesThreshold,
    );
  }

  Future<void> _calculate() async {
    if (!(_formKey.currentState?.validate() ?? false)) {
      return;
    }

    final answers = {
      for (final entry in _answers.entries) entry.key: entry.value!,
    };

    final result = AnemiaRiskCalculator.calculate(
      questions: _questions,
      answers: answers,
      riskYesThreshold: _riskYesThreshold,
    );

    if (result == null) {
      return;
    }

    setState(() => _result = result);

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
      await ref.read(anemiaScreeningRepositoryProvider).submit(
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
      _isSaving = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final contentAsync =
        ref.watch(cekRisikoAnemiaContentProvider(widget.menuSlug));

    final resolved = _resolveFromContent(contentAsync.valueOrNull);
    final signature = resolved.questions.map((q) => q.id).join('|');
    if (_questionsSignature != signature) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) {
          return;
        }
        _applyQuestionnaire(
          questions: resolved.questions,
          riskYesThreshold: resolved.threshold,
        );
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
                      key: ValueKey(_questions[i].id),
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
                  if (_result != null) ...[
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
          _AnemiaResultCard(result: _result!),
        ],
        const SizedBox(height: 16),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Text(
              'Interpretasi: jawaban "Ya" ≥ $_riskYesThreshold dari '
              '${_questions.length} pertanyaan menandakan risiko anemia. '
              'Diagnosis pasti memerlukan pemeriksaan Hb (≥ 12 g/dL dianggap '
              'tidak anemia pada remaja putri). Hasil ini bersifat skrining awal.',
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
                height: 1.4,
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _AnemiaIntroText extends ConsumerWidget {
  const _AnemiaIntroText({required this.menuSlug});

  final String menuSlug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final introStyle = TextStyle(
      color: Colors.grey.shade700,
      height: 1.5,
    );

    final contentAsync = ref.watch(cekRisikoAnemiaIntroContentProvider(menuSlug));

    Widget? introFromSnapshot(KebutuhanMuContentSnapshot? snapshot) {
      final content = snapshot?.content;
      final excerpt = content?.excerpt?.trim();
      if (excerpt != null && excerpt.isNotEmpty) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(excerpt, style: introStyle),
            if (snapshot?.isFromCache == true) ...[
              const SizedBox(height: 8),
              Text(
                'Konten tersimpan (offline).',
                style: TextStyle(
                  color: Colors.amber.shade900,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ],
        );
      }

      final body = content?.body?.trim();
      if (body != null && body.isNotEmpty) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            KebutuhanMuMenuDescription(description: body),
            if (snapshot?.isFromCache == true) ...[
              const SizedBox(height: 8),
              Text(
                'Konten tersimpan (offline).',
                style: TextStyle(
                  color: Colors.amber.shade900,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ],
        );
      }

      return null;
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
  final AnemiaScreeningQuestion question;
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

class _AnemiaResultCard extends StatelessWidget {
  const _AnemiaResultCard({required this.result});

  final AnemiaRiskResult result;

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
            const SizedBox(height: 12),
            Text(
              '${result.yesCount} dari ${result.totalQuestions} indikator',
              style: TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                color: _accentColor,
                height: 1,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              'Jawaban "Ya"',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade600,
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
                result.categoryLabel,
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
              result.recommendation,
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
