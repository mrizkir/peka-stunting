import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_theme.dart';
import '../../kebutuhan_mu/kebutuhan_mu_config.dart';
import '../domain/bmi_calculator.dart';
import '../domain/lila_calculator.dart';

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

class CekLilaScreen extends StatefulWidget {
  const CekLilaScreen({
    super.key,
    required this.menuSlug,
  });

  final String menuSlug;

  @override
  State<CekLilaScreen> createState() => _CekLilaScreenState();
}

class _CekLilaScreenState extends State<CekLilaScreen> {
  final _formKey = GlobalKey<FormState>();
  final _lilaController = TextEditingController();

  LilaResult? _result;

  String get _groupLabel =>
      KebutuhanMuConfig.groupTitles[widget.menuSlug] ?? 'Kebutuhanmu';

  @override
  void dispose() {
    _lilaController.dispose();
    super.dispose();
  }

  void _calculate() {
    if (!(_formKey.currentState?.validate() ?? false)) {
      return;
    }

    final lila = double.parse(_lilaController.text.replaceAll(',', '.'));

    setState(() {
      _result = LilaCalculator.calculate(circumferenceCm: lila);
    });
  }

  void _reset() {
    _lilaController.clear();
    setState(() => _result = null);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Cek LILA'),
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
          Text(
            'LILA (Lingkar Lengan Atas) adalah ukuran lingkar lengan bagian '
            'atas (lingkar di pertengahan antara bahu dan siku) yang digunakan '
            'untuk menilai status gizi remaja apakah mengalami kekurangan '
            'energi kronis (KEK), yaitu LILA < 23,5 cm. LILA kecil '
            'menunjukkan tubuh kekurangan asupan energi dan protein dalam waktu '
            'lama. Akibatnya, risiko stunting meningkat.',
            style: TextStyle(color: Colors.grey.shade700, height: 1.5),
          ),
          const SizedBox(height: 20),
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
                      onPressed: _calculate,
                      child: const Text('Klik Hasil'),
                    ),
                    if (_result != null) ...[
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
            _LilaResultCard(result: _result!),
          ],
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Text(
                'Interpretasi: LILA < ${LilaCalculator.normalMinimumCm} cm '
                'berisiko KEK; LILA ≥ ${LilaCalculator.normalMinimumCm} cm '
                'status gizi relatif normal. Hasil ini bersifat skrining awal '
                '— konsultasikan dengan tenaga kesehatan untuk penilaian '
                'lebih lanjut.',
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
  const _LilaResultCard({required this.result});

  final LilaResult result;

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
