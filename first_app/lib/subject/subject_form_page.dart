import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class SubjectFormPage extends StatefulWidget {
  final Map? subjectData;
  final Map userData;
  final String? preselectedCampusID;
  final String? preselectedClassesID;

  const SubjectFormPage({
    super.key,
    this.subjectData,
    required this.userData,
    this.preselectedCampusID,
    this.preselectedClassesID,
  });

  @override
  State<SubjectFormPage> createState() => _SubjectFormPageState();
}

class _SubjectFormPageState extends State<SubjectFormPage> {
  final _formKey        = GlobalKey<FormState>();
  final _subjectCtrl    = TextEditingController();
  final _codeCtrl       = TextEditingController();
  final _authorCtrl     = TextEditingController();
  final _passmarkCtrl   = TextEditingController();
  final _finalmarkCtrl  = TextEditingController();

  List _campuses = [];
  List _classes  = [];
  List _teachers = [];

  String? _selectedCampus;
  String? _selectedClass;
  String? _selectedTeacher;
  String? _selectedType;
  bool _isLoading = false;

  static const List<String> _types = ['Compulsory', 'Optional'];

  @override
  void initState() {
    super.initState();
    _isLoading = true;

    if (widget.subjectData != null) {
      final d = widget.subjectData!;
      _subjectCtrl.text   = (d['subject']        ?? '').toString();
      _codeCtrl.text      = (d['subject_code']   ?? '').toString();
      _authorCtrl.text    = (d['subject_author']  ?? '').toString();
      _passmarkCtrl.text  = (d['passmark']        ?? '').toString();
      _finalmarkCtrl.text = (d['finalmark']       ?? '').toString();
      _selectedCampus     = (d['campusID']        ?? '').toString();
      _selectedClass      = (d['classesID']       ?? '').toString();
      _selectedTeacher    = (d['teacherID']      ?? '').toString().isEmpty || d['teacherID'].toString() == '0' ? null : d['teacherID'].toString();
      
      final rawType = (d['type'] ?? '').toString();
      if (rawType == '1' || rawType.toLowerCase() == 'compulsory') {
        _selectedType = 'Compulsory';
      } else if (rawType == '0' || rawType.toLowerCase() == 'optional') {
        _selectedType = 'Optional';
      } else {
        _selectedType = _types.contains(rawType) ? rawType : 'Compulsory';
      }
    } else {
      _selectedCampus = widget.preselectedCampusID;
      _selectedClass  = widget.preselectedClassesID;
      _selectedType   = 'Compulsory';
    }

    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    await _fetchCampuses();
    if (_selectedCampus != null && _selectedCampus != '0') {
      await _fetchClasses(_selectedCampus!);
      await _fetchTeachers(_selectedCampus!);
    }

    // Guard against invalid selections causing assertions
    if (_selectedCampus != null && !_campuses.any((c) => c['campusID'].toString() == _selectedCampus)) {
      _selectedCampus = null;
    }
    if (_selectedClass != null && !_classes.any((c) => c['classesID'].toString() == _selectedClass)) {
      _selectedClass = null;
    }
    if (_selectedTeacher != null && !_teachers.any((t) => t['teacherID'].toString() == _selectedTeacher)) {
      _selectedTeacher = null;
    }

    if (mounted) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  void dispose() {
    _subjectCtrl.dispose();
    _codeCtrl.dispose();
    _authorCtrl.dispose();
    _passmarkCtrl.dispose();
    _finalmarkCtrl.dispose();
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
        Uri.parse('${base}api/teacher'),
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
    if (_selectedType == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please select a type')));
      return;
    }

    setState(() => _isLoading = true);

    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';

    final isEdit = widget.subjectData != null;
    final apiUrl = isEdit ? '${base}api/subject_update' : '${base}api/subject_add';

    final body = <String, dynamic>{
      'campusID':       _selectedCampus,
      'classesID':      _selectedClass,
      'teacherID':      _selectedTeacher ?? '0',
      'subject':        _subjectCtrl.text.trim(),
      'subject_code':   _codeCtrl.text.trim(),
      'subject_author': _authorCtrl.text.trim(),
      'type':           _selectedType,
      'passmark':       _passmarkCtrl.text.trim(),
      'finalmark':      _finalmarkCtrl.text.trim(),

      'adminID': widget.userData['adminID'] ?? 1,
      'create_userID': _getCreateUserID(),
      'create_username': widget.userData['username'] ?? widget.userData['name'] ?? 'admin',
      'create_usertype': widget.userData['user_type'] ?? 'Admin',

      if (isEdit) 'subjectID': widget.subjectData!['subjectID'],
    };

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );

      if (response.body.trim().isEmpty) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Server returned an empty response. Please try again.')),
        );
        return;
      }

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
      dev.log('Save subject error: $e');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error saving subject: $e')),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.subjectData == null ? 'Add Subject' : 'Edit Subject'),
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

                    // Teacher (Optional)
                    DropdownButtonFormField<String>(
                      value: _selectedTeacher,
                      decoration: const InputDecoration(labelText: 'Teacher (Optional)'),
                      items: [
                        const DropdownMenuItem<String>(
                          value: null,
                          child: Text('Select Teacher'),
                        ),
                        ..._teachers.map((t) => DropdownMenuItem<String>(
                              value: t['teacherID'].toString(),
                              child: Text(t['name'] ?? 'Teacher'),
                            )),
                      ],
                      onChanged: (value) => setState(() => _selectedTeacher = value),
                    ),
                    const SizedBox(height: 12),

                    // Subject name
                    TextFormField(
                      controller: _subjectCtrl,
                      decoration: const InputDecoration(labelText: 'Subject Name'),
                      validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Subject code
                    TextFormField(
                      controller: _codeCtrl,
                      decoration: const InputDecoration(labelText: 'Subject Code'),
                      validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Type
                    DropdownButtonFormField<String>(
                      value: _selectedType,
                      decoration: const InputDecoration(labelText: 'Type'),
                      items: _types.map((t) => DropdownMenuItem<String>(
                            value: t,
                            child: Text(t),
                          )).toList(),
                      onChanged: (value) => setState(() => _selectedType = value),
                      validator: (v) => v == null ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Pass mark
                    TextFormField(
                      controller: _passmarkCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Pass Mark'),
                      validator: (v) {
                        if (v == null || v.trim().isEmpty) return 'Required';
                        if (int.tryParse(v.trim()) == null) return 'Enter a valid number';
                        return null;
                      },
                    ),
                    const SizedBox(height: 12),

                    // Final mark
                    TextFormField(
                      controller: _finalmarkCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Final Mark'),
                      validator: (v) {
                        if (v == null || v.trim().isEmpty) return 'Required';
                        if (int.tryParse(v.trim()) == null) return 'Enter a valid number';
                        return null;
                      },
                    ),
                    const SizedBox(height: 12),

                    // Author (optional)
                    TextFormField(
                      controller: _authorCtrl,
                      decoration: const InputDecoration(labelText: 'Author (optional)'),
                    ),
                    const SizedBox(height: 20),

                    ElevatedButton(
                      onPressed: _save,
                      child: Text(widget.subjectData == null ? 'Add Subject' : 'Update Subject'),
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
