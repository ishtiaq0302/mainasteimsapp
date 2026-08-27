import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class SectionFormPage extends StatefulWidget {
  final Map? sectionData;
  final Map userData;
  final String? preselectedCampusID;
  final String? preselectedClassesID;

  const SectionFormPage({
    super.key,
    this.sectionData,
    required this.userData,
    this.preselectedCampusID,
    this.preselectedClassesID,
  });

  @override
  State<SectionFormPage> createState() => _SectionFormPageState();
}

class _SectionFormPageState extends State<SectionFormPage> {
  final _formKey         = GlobalKey<FormState>();
  final _sectionCtrl     = TextEditingController();
  final _categoryCtrl    = TextEditingController();
  final _capacityCtrl    = TextEditingController();
  final _noteCtrl        = TextEditingController();

  List _campuses = [];
  List _classes  = [];
  List _teachers = [];

  String? _selectedCampus;
  String? _selectedClass;
  String? _selectedTeacher;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _fetchCampuses();

    if (widget.sectionData != null) {
      final d = widget.sectionData!;
      _sectionCtrl.text    = (d['section']  ?? '').toString();
      _categoryCtrl.text   = (d['category'] ?? '').toString();
      _capacityCtrl.text   = (d['capacity'] ?? '').toString();
      _noteCtrl.text       = (d['note']     ?? '').toString();
      _selectedCampus      = (d['campusID']  ?? '').toString();
      _selectedClass       = (d['classesID'] ?? '').toString();
      _selectedTeacher     = (d['teacherID'] ?? '').toString();
    } else {
      _selectedCampus  = widget.preselectedCampusID;
      _selectedClass   = widget.preselectedClassesID;
    }
  }

  @override
  void dispose() {
    _sectionCtrl.dispose();
    _categoryCtrl.dispose();
    _capacityCtrl.dispose();
    _noteCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchCampuses() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/metadata'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': 0, 'adminID': widget.userData['adminID'] ?? 1}),
      );
      final result = jsonDecode(res.body);
      if (result['status'] == true) {
        setState(() => _campuses = result['data']['campuses'] ?? []);
        if (_selectedCampus != null) {
          await _fetchClasses(_selectedCampus!);
          if (_selectedClass != null) await _fetchTeachers(_selectedCampus!);
        }
      }
    } catch (e) {
      dev.log('Fetch campuses error: $e');
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
      if (result['status'] == true) {
        setState(() {
          _classes = result['data'] ?? [];
          if (_selectedClass != null &&
              _classes.every((c) => c['classesID'].toString() != _selectedClass)) {
            _selectedClass = null;
          }
        });
      }
    } catch (e) {
      dev.log('Fetch classes error: $e');
    }
  }

  Future<void> _fetchTeachers(String campusID) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/teachers'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': campusID, 'adminID': widget.userData['adminID'] ?? 1}),
      );
      final result = jsonDecode(res.body);
      if (result['status'] == true) {
        setState(() {
          _teachers = result['data'] ?? [];
          if (_selectedTeacher != null &&
              _teachers.every((t) => t['teacherID'].toString() != _selectedTeacher)) {
            _selectedTeacher = null;
          }
        });
      }
    } catch (e) {
      dev.log('Fetch teachers error: $e');
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedCampus == null || _selectedCampus == '0') {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please select a campus')));
      return;
    }
    if (_selectedClass == null || _selectedClass == '0') {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please select a class')));
      return;
    }
    if (_selectedTeacher == null || _selectedTeacher == '0') {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please select a teacher')));
      return;
    }

    setState(() => _isLoading = true);

    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';

    final isEdit  = widget.sectionData != null;
    final apiUrl  = isEdit ? '${base}api/section_update' : '${base}api/section_add';

    final body = {
      'campusID':  _selectedCampus,
      'classesID': _selectedClass,
      'teacherID': _selectedTeacher,
      'section':   _sectionCtrl.text.trim(),
      'category':  _categoryCtrl.text.trim(),
      'capacity':  _capacityCtrl.text.trim(),
      'note':      _noteCtrl.text.trim(),
      'adminID':   widget.userData['adminID'] ?? 1,
      if (isEdit) 'sectionID': widget.sectionData!['sectionID'],
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
      dev.log('Save section error: $e');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Connection error while saving section')),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.sectionData == null ? 'Add Section' : 'Edit Section'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: ListView(
                  children: [
                    // Campus
                    DropdownButtonFormField<String>(
                      value: _selectedCampus,
                      decoration: const InputDecoration(labelText: 'Campus'),
                      items: _campuses.map((c) => DropdownMenuItem<String>(
                            value: c['campusID'].toString(),
                            child: Text(c['name'] ?? 'Campus'),
                          )).toList(),
                      onChanged: (value) {
                        setState(() {
                          _selectedCampus  = value;
                          _selectedClass   = null;
                          _selectedTeacher = null;
                          _classes  = [];
                          _teachers = [];
                        });
                        if (value != null) {
                          _fetchClasses(value);
                          _fetchTeachers(value);
                        }
                      },
                      validator: (v) => v == null || v == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Class
                    DropdownButtonFormField<String>(
                      value: _selectedClass,
                      decoration: const InputDecoration(labelText: 'Class'),
                      items: _classes.map((c) => DropdownMenuItem<String>(
                            value: c['classesID'].toString(),
                            child: Text(c['classes'] ?? 'Class'),
                          )).toList(),
                      onChanged: (value) => setState(() => _selectedClass = value),
                      validator: (v) => v == null || v == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Teacher
                    DropdownButtonFormField<String>(
                      value: _selectedTeacher,
                      decoration: const InputDecoration(labelText: 'Teacher'),
                      items: _teachers.map((t) => DropdownMenuItem<String>(
                            value: t['teacherID'].toString(),
                            child: Text(t['name'] ?? 'Teacher'),
                          )).toList(),
                      onChanged: (value) => setState(() => _selectedTeacher = value),
                      validator: (v) => v == null || v == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Section name
                    TextFormField(
                      controller: _sectionCtrl,
                      decoration: const InputDecoration(labelText: 'Section Name'),
                      validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Category
                    TextFormField(
                      controller: _categoryCtrl,
                      decoration: const InputDecoration(labelText: 'Category'),
                      validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Capacity
                    TextFormField(
                      controller: _capacityCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Capacity'),
                      validator: (v) {
                        if (v == null || v.trim().isEmpty) return 'Required';
                        final n = int.tryParse(v.trim());
                        if (n == null || n <= 0) return 'Enter a valid positive number';
                        return null;
                      },
                    ),
                    const SizedBox(height: 12),

                    // Note
                    TextFormField(
                      controller: _noteCtrl,
                      maxLines: 3,
                      decoration: const InputDecoration(labelText: 'Note'),
                    ),
                    const SizedBox(height: 20),

                    ElevatedButton(
                      onPressed: _save,
                      child: Text(widget.sectionData == null ? 'Add Section' : 'Update Section'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
