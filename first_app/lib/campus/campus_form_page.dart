import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

/// Add or edit a campus.
/// Pass [campusData] to edit an existing campus; leave null to add a new one.
class CampusFormPage extends StatefulWidget {
  final Map?  campusData; // non-null = edit mode
  final Map   userData;

  const CampusFormPage({
    super.key,
    this.campusData,
    required this.userData,
  });

  @override
  State<CampusFormPage> createState() => _CampusFormPageState();
}

class _CampusFormPageState extends State<CampusFormPage> {
  final _formKey   = GlobalKey<FormState>();
  final _nameCtrl  = TextEditingController();

  bool _isLoading = false;

  static const Color _primary = Color(0xFF1565C0);

  @override
  void initState() {
    super.initState();
    if (widget.campusData != null) {
      _nameCtrl.text = (widget.campusData!['name'] ?? '').toString();
    }
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';

    final isEdit = widget.campusData != null;
    final apiUrl = isEdit ? '${base}api/campus_update' : '${base}api/campus_add';

    final body = <String, dynamic>{
      'name':    _nameCtrl.text.trim(),
      'adminID': widget.userData['adminID'] ?? 1,

      'create_userID': _getCreateUserID(),
      'create_username': widget.userData['username'] ?? widget.userData['name'] ?? 'admin',
      'create_usertype': widget.userData['user_type'] ?? 'Admin',

      if (isEdit) 'campusID': widget.campusData!['campusID'],
    };

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;

      if (result['status'] == true) {
        Navigator.pop(context, true);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Saved successfully')),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Save failed')),
        );
      }
    } catch (e) {
      dev.log('Save campus error: $e');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.campusData != null;

    return Scaffold(
      appBar: AppBar(
        title: Text(isEdit ? 'Edit Campus' : 'Add Campus'),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [_primary, Color(0xFF42A5F5)]),
          ),
        ),
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // ── Header icon ─────────────────────────────────────
                    Center(
                      child: CircleAvatar(
                        radius: 36,
                        backgroundColor: _primary.withOpacity(0.12),
                        child: const Icon(Icons.location_city, size: 40, color: _primary),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // ── Campus name ──────────────────────────────────────
                    TextFormField(
                      controller: _nameCtrl,
                      textCapitalization: TextCapitalization.words,
                      decoration: InputDecoration(
                        labelText: 'Campus Name *',
                        prefixIcon: const Icon(Icons.location_city),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      validator: (v) => v == null || v.trim().isEmpty ? 'Campus name is required' : null,
                    ),
                    const SizedBox(height: 28),

                    // ── Save button ──────────────────────────────────────
                    ElevatedButton.icon(
                      onPressed: _save,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      icon: Icon(isEdit ? Icons.save : Icons.add_circle_outline),
                      label: Text(isEdit ? 'Update Campus' : 'Add Campus', style: const TextStyle(fontSize: 16)),
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  int _getCreateUserID() {
    final d = widget.userData;
    final keys = ['systemadminID', 'userID', 'teacherID', 'parentsID', 'studentID'];
    for (var k in keys) {
      if (d[k] != null) return int.tryParse(d[k].toString()) ?? 1;
    }
    return 1;
  }
}
