import 'dart:convert';
import 'dart:developer' as dev;
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

/// Add or edit a syllabus entry (with file upload support).
/// Pass [syllabusData] to edit an existing record.
class SyllabusFormPage extends StatefulWidget {
  final Map?    syllabusData;
  final Map     userData;
  final String? preselectedCampusID;
  final String? preselectedClassesID;
  final String? preselectedSchoolyearID;

  const SyllabusFormPage({
    super.key,
    this.syllabusData,
    required this.userData,
    this.preselectedCampusID,
    this.preselectedClassesID,
    this.preselectedSchoolyearID,
  });

  @override
  State<SyllabusFormPage> createState() => _SyllabusFormPageState();
}

class _SyllabusFormPageState extends State<SyllabusFormPage> {
  final _formKey     = GlobalKey<FormState>();
  final _titleCtrl   = TextEditingController();
  final _descCtrl    = TextEditingController();

  List _campuses    = [];
  List _classes     = [];
  List _schoolyears = [];

  String? _selCampus;
  String? _selClass;
  String? _selSchoolyear;

  // File
  File?   _pickedFile;
  String? _pickedFileName;
  String? _existingFileName; // displayed in edit mode when no new file chosen

  bool _isLoading = false;

  static const Color _primary = Color(0xFF4A148C);
  static const Color _accent  = Color(0xFFAB47BC);

  @override
  void initState() {
    super.initState();
    if (widget.syllabusData != null) {
      final d = widget.syllabusData!;
      _titleCtrl.text     = (d['title']       ?? '').toString();
      _descCtrl.text      = (d['description'] ?? '').toString();
      _selCampus          = (d['campusID']     ?? '').toString().isNotEmpty ? d['campusID'].toString() : null;
      _selClass           = (d['classesID']    ?? '').toString().isNotEmpty && d['classesID'].toString() != '0' ? d['classesID'].toString() : null;
      _selSchoolyear      = (d['schoolyearID'] ?? '').toString().isNotEmpty && d['schoolyearID'].toString() != '0' ? d['schoolyearID'].toString() : null;
      _existingFileName   = (d['originalfile'] ?? '').toString().isNotEmpty ? d['originalfile'].toString() : null;
    } else {
      _selCampus          = widget.preselectedCampusID;
      _selClass           = widget.preselectedClassesID;
      _selSchoolyear      = widget.preselectedSchoolyearID;
    }
    _fetchMeta();
  }

  @override
  void dispose() {
    _titleCtrl.dispose();
    _descCtrl.dispose();
    super.dispose();
  }

  // ── Meta ────────────────────────────────────────────────────────────────
  Future<void> _fetchMeta() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final campusRes = await http.post(
        Uri.parse('${base}api/campus'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'adminID': widget.userData['adminID'] ?? 1}),
      );
      final campusResult = jsonDecode(campusRes.body);
      if (!mounted) return;
      if (campusResult['status'] == true) {
        setState(() => _campuses = campusResult['data'] ?? []);
      }

      final yearRes = await http.post(
        Uri.parse('${base}api/schoolyears'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'adminID': widget.userData['adminID'] ?? 1}),
      );
      final yearResult = jsonDecode(yearRes.body);
      if (!mounted) return;
      if (yearResult['status'] == true) {
        setState(() => _schoolyears = yearResult['data'] ?? []);
      }

      if (_selCampus != null) await _fetchClasses(_selCampus!);
    } catch (e) {
      dev.log('Syllabus form meta error: $e');
    }
  }

  Future<void> _fetchClasses(String campusID) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/classes'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': campusID, 'adminID': widget.userData['adminID'] ?? 1}),
      );
      final result = jsonDecode(res.body);
      if (!mounted) return;
      setState(() {
        _classes = result['data'] ?? [];
        if (_selClass != null && _classes.every((c) => c['classesID'].toString() != _selClass)) {
          _selClass = null;
        }
      });
    } catch (e) {
      dev.log('Syllabus form fetch classes error: $e');
    }
  }

  // ── File picker ───────────────────────────────────────────────────────────
  Future<void> _pickFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'ppt', 'csv', 'jpg', 'jpeg', 'png'],
    );
    if (result != null && result.files.single.path != null) {
      setState(() {
        _pickedFile     = File(result.files.single.path!);
        _pickedFileName = result.files.single.name;
      });
    }
  }

  // ── Save ──────────────────────────────────────────────────────────────────
  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    if (_selCampus == null) { _snack('Please select a campus'); return; }
    if (_selClass == null)  { _snack('Please select a class');  return; }

    final isEdit = widget.syllabusData != null;

    // For add, file is mandatory
    if (!isEdit && _pickedFile == null) {
      _snack('Please attach a syllabus file');
      return;
    }

    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';

    final apiUrl = isEdit ? '${base}api/syllabus_update' : '${base}api/syllabus_add';

    final body = <String, dynamic>{
      'campusID':     _selCampus,
      'classesID':    _selClass,
      'title':        _titleCtrl.text.trim(),
      'description':  _descCtrl.text.trim(),
      
      'adminID': widget.userData['adminID'] ?? 1,
      'create_userID': _getCreateUserID(),
      'create_username': widget.userData['username'] ?? widget.userData['name'] ?? 'admin',
      'create_usertype': widget.userData['user_type'] ?? 'Admin',

      if (_selSchoolyear != null) 'schoolyearID': _selSchoolyear,
      if (isEdit) 'syllabusID': widget.syllabusData!['syllabusID'],
    };

    // Encode file as base64 if selected
    if (_pickedFile != null && _pickedFileName != null) {
      final bytes  = await _pickedFile!.readAsBytes();
      body['file_base64'] = base64Encode(bytes);
      body['file_name']   = _pickedFileName;
    }

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
          SnackBar(content: Text(result['message'] ?? 'Saved')),
        );
      } else {
        _snack(result['message'] ?? 'Save failed');
      }
    } catch (e) {
      dev.log('Save syllabus error: $e');
      if (!mounted) return;
      _snack('Error: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _snack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  InputDecoration _decor(String label, IconData icon) => InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, size: 20),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        isDense: true,
        contentPadding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
      );

  Widget _sectionLabel(String label) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Text(label,
            style: const TextStyle(
              fontWeight: FontWeight.bold,
              color: _primary,
              fontSize: 13,
              letterSpacing: 0.5,
            )),
      );

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.syllabusData != null;

    return Scaffold(
      appBar: AppBar(
        title: Text(isEdit ? 'Edit Syllabus' : 'Add Syllabus'),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [_primary, _accent]),
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
                    // Header icon
                    Center(
                      child: CircleAvatar(
                        radius: 36,
                        backgroundColor: _primary.withOpacity(0.12),
                        child: const Icon(Icons.menu_book, size: 40, color: _primary),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // ── Location ──────────────────────────────────────────────
                    _sectionLabel('Campus & Class'),
                    DropdownButtonFormField<String>(
                      value: _selCampus,
                      decoration: _decor('Campus *', Icons.location_city),
                      items: _campuses.map((c) => DropdownMenuItem<String>(
                            value: c['campusID'].toString(),
                            child: Text(c['name'] ?? 'Campus'),
                          )).toList(),
                      onChanged: (v) {
                        setState(() {
                          _selCampus = v;
                          _selClass  = null;
                          _classes   = [];
                        });
                        if (v != null) _fetchClasses(v);
                      },
                      validator: (v) => v == null ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    DropdownButtonFormField<String>(
                      value: _selClass,
                      decoration: _decor('Class *', Icons.class_),
                      items: _classes.map((c) => DropdownMenuItem<String>(
                            value: c['classesID'].toString(),
                            child: Text(c['classes'] ?? 'Class'),
                          )).toList(),
                      onChanged: (v) => setState(() => _selClass = v),
                      validator: (v) => v == null ? 'Required' : null,
                    ),
                    const SizedBox(height: 20),

                    // ── Subject Name ──────────────────────────────────────────
                    _sectionLabel('Subject Name'),
                    TextFormField(
                      controller: _titleCtrl,
                      textCapitalization: TextCapitalization.words,
                      decoration: _decor('Subject Name *', Icons.book),
                      validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    TextFormField(
                      controller: _descCtrl,
                      maxLines: 4,
                      decoration: _decor('Description *', Icons.description),
                      validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
                    ),
                    const SizedBox(height: 20),

                    // ── File upload ────────────────────────────────────────────
                    _sectionLabel('Syllabus File'),
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade400),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Show existing file in edit mode
                          if (isEdit && _existingFileName != null && _pickedFileName == null) ...[
                            Row(children: [
                              const Icon(Icons.attach_file, color: Colors.grey),
                              const SizedBox(width: 6),
                              Flexible(child: Text(_existingFileName!, style: const TextStyle(color: Colors.grey))),
                            ]),
                            const SizedBox(height: 8),
                            Text('(upload new file to replace)', style: TextStyle(color: Colors.grey.shade500, fontSize: 12)),
                            const SizedBox(height: 8),
                          ],
                          // New file selected
                          if (_pickedFileName != null) ...[
                            Row(children: [
                              const Icon(Icons.check_circle, color: Colors.green),
                              const SizedBox(width: 6),
                              Flexible(child: Text(_pickedFileName!, style: const TextStyle(color: Colors.green))),
                            ]),
                            const SizedBox(height: 8),
                          ],
                          ElevatedButton.icon(
                            onPressed: _pickFile,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: _primary.withOpacity(0.1),
                              foregroundColor: _primary,
                            ),
                            icon: const Icon(Icons.upload_file),
                            label: Text(_pickedFileName != null ? 'Change File' : (isEdit ? 'Replace File (optional)' : 'Choose File *')),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Allowed: PDF, DOC, DOCX, XLS, XLSX, PPT, TXT, CSV, JPG, PNG',
                            style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 28),

                    // Save
                    ElevatedButton.icon(
                      onPressed: _save,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      icon: Icon(isEdit ? Icons.save : Icons.add_circle_outline),
                      label: Text(isEdit ? 'Update Syllabus' : 'Add Syllabus', style: const TextStyle(fontSize: 16)),
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
