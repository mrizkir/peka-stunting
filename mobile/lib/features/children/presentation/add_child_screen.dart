import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../data/children_repository.dart';

class AddChildScreen extends ConsumerStatefulWidget {
  const AddChildScreen({super.key});

  @override
  ConsumerState<AddChildScreen> createState() => _AddChildScreenState();
}

class _AddChildScreenState extends ConsumerState<AddChildScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _villageController = TextEditingController();
  final _posyanduController = TextEditingController();
  final _guardianNameController = TextEditingController();
  final _guardianPhoneController = TextEditingController();
  String _gender = 'L';
  DateTime? _birthDate;
  bool _isSaving = false;

  @override
  void dispose() {
    _nameController.dispose();
    _villageController.dispose();
    _posyanduController.dispose();
    _guardianNameController.dispose();
    _guardianPhoneController.dispose();
    super.dispose();
  }

  Future<void> _pickBirthDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime(now.year - 2),
      firstDate: DateTime(now.year - 6),
      lastDate: now,
    );
    if (picked != null) setState(() => _birthDate = picked);
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate() || _birthDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Lengkapi data anak dan tanggal lahir.')),
      );
      return;
    }

    setState(() => _isSaving = true);
    try {
      final child = await ref.read(childrenRepositoryProvider).createChild({
        'name': _nameController.text.trim(),
        'gender': _gender,
        'birth_date': _birthDate!.toIso8601String().split('T').first,
        'village': _villageController.text.trim().isEmpty
            ? null
            : _villageController.text.trim(),
        'posyandu': _posyanduController.text.trim().isEmpty
            ? null
            : _posyanduController.text.trim(),
        'guardian': {
          'name': _guardianNameController.text.trim(),
          'phone': _guardianPhoneController.text.trim().isEmpty
              ? null
              : _guardianPhoneController.text.trim(),
        },
      });
      if (mounted) context.pop(child.id);
    } on ApiException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(error.message)),
        );
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Tambah Anak')),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Form(
            key: _formKey,
            child: Column(
              children: [
                TextFormField(
                  controller: _nameController,
                  decoration: const InputDecoration(labelText: 'Nama anak'),
                  validator: (v) =>
                      v == null || v.trim().isEmpty ? 'Wajib diisi' : null,
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: _gender,
                  decoration: const InputDecoration(labelText: 'Jenis kelamin'),
                  items: const [
                    DropdownMenuItem(value: 'L', child: Text('Laki-laki')),
                    DropdownMenuItem(value: 'P', child: Text('Perempuan')),
                  ],
                  onChanged: (value) {
                    if (value != null) setState(() => _gender = value);
                  },
                ),
                const SizedBox(height: 12),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('Tanggal lahir'),
                  subtitle: Text(
                    _birthDate == null
                        ? 'Belum dipilih'
                        : _birthDate!.toIso8601String().split('T').first,
                  ),
                  trailing: const Icon(Icons.calendar_today),
                  onTap: _pickBirthDate,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _villageController,
                  decoration: const InputDecoration(labelText: 'Desa'),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _posyanduController,
                  decoration: const InputDecoration(labelText: 'Posyandu'),
                ),
                const SizedBox(height: 20),
                const Align(
                  alignment: Alignment.centerLeft,
                  child: Text(
                    'Data wali',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _guardianNameController,
                  decoration: const InputDecoration(labelText: 'Nama wali'),
                  validator: (v) =>
                      v == null || v.trim().isEmpty ? 'Wajib diisi' : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _guardianPhoneController,
                  decoration: const InputDecoration(labelText: 'No. HP wali'),
                  keyboardType: TextInputType.phone,
                ),
                const SizedBox(height: 24),
                FilledButton(
                  onPressed: _isSaving ? null : _submit,
                  child: _isSaving
                      ? const CircularProgressIndicator()
                      : const Text('Simpan'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
