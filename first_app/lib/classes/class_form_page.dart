import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class ClassFormPage extends StatefulWidget {
  final Map? classData;
  final Map userData;

  const ClassFormPage({super.key, this.classData, required this.userData});

  @override
  State<ClassFormPage> createState() => _ClassFormPageState();
}

class _ClassFormPageState extends State<ClassFormPage> {
  final _formKey = GlobalKey<FormState>();
  final _classNameController = TextEditingController();
  final _classNumericController = TextEditingController();
  final _noteController = TextEditingController();

  List _campuses = [];
  List _teachers = [];
  String? _selectedCampus;
  String? _selectedTeacher;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    if (widget.classData != null) {
      _classNameController.text = (widget.classData!['classes'] ?? '').toString();
      _classNumericController.text = (widget.classData!['classes_numeric'] ?? '').toString();
      _noteController.text = (widget.classData!['note'] ?? '').toString();
      _selectedCampus = (widget.classData!['campusID'] ?? '').toString();
      _selectedTeacher = (widget.classData!['teacherID'] ?? '').toString();
    }
    _initData();
  }

  @override
  void dispose() {
    _classNameController.dispose();
    _classNumericController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  Future<void> _initData() async {
    setState(() => _isLoading = true);
    await _fetchCampuses();
    await _fetchTeachers(_selectedCampus ?? '0');
    if (mounted) setState(() => _isLoading = false);
  }

  Future<void> _fetchCampuses() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/metadata';

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': 0}),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        _campuses = result['data']['campuses'] ?? [];
      }
    } catch (e) {
      dev.log('Fetch campuses error: $e');
    }
  }

  Future<void> _fetchTeachers(String campusID) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/teachers';

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID': campusID == '0' ? '' : campusID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );

      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        List teachersList = result['data'] ?? [];
        _teachers = teachersList;
        if (_selectedTeacher != null &&
            _selectedTeacher != '0' &&
            !_teachers.any((t) => t['teacherID'].toString() == _selectedTeacher)) {
          // If teacher is not in current campus list, search all teachers
          final allTeachersRes = await http.post(
            Uri.parse(apiUrl),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({'adminID': widget.userData['adminID'] ?? 1}),
          );
          final allResult = jsonDecode(allTeachersRes.body);
          if (allResult['status'] == true) {
            _teachers = allResult['data'] ?? [];
          }
        }
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

    if (_selectedTeacher == null || _selectedTeacher == '0') {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please select a teacher')));
      return;
    }

    setState(() => _isLoading = true);

    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';

    final String apiUrl = widget.classData == null
        ? '${base}api/classes_add'
        : '${base}api/classes_update';

    final body = <String, dynamic>{
      'campusID': _selectedCampus,
      'classes': _classNameController.text.trim(),
      'classes_numeric': _classNumericController.text.trim(),
      'teacherID': _selectedTeacher,
      'note': _noteController.text.trim(),
      
      'adminID': widget.userData['adminID'] ?? 1,
      'create_userID': _getCreateUserID(),
      'create_username': widget.userData['username'] ?? widget.userData['name'] ?? 'admin',
      'create_usertype': widget.userData['user_type'] ?? 'Admin',

      if (widget.classData != null) 'classesID': widget.classData!['classesID'] ?? widget.classData!['classID'],
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

      Map result;
      try {
        result = jsonDecode(response.body);
      } catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Server returned an invalid response: ${response.body}')),
        );
        return;
      }
      
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
      dev.log('Save class error: $e');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error saving class: $e')),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.classData == null ? 'Add Class' : 'Edit Class'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: ListView(
                  children: [
                    DropdownButtonFormField<String>(
                      initialValue: _selectedCampus,
                      decoration: const InputDecoration(labelText: 'Campus'),
                      items: _campuses
                          .map(
                            (campus) => DropdownMenuItem<String>(
                              value: campus['campusID'].toString(),
                              child: Text(campus['name'] ?? 'Campus'),
                            ),
                          )
                          .toList(),
                      onChanged: (value) {
                        setState(() {
                          _selectedCampus = value;
                          _selectedTeacher = null;
                        });
                        if (value != null && value != '0') {
                          _fetchTeachers(value).then((_) {
                            if (mounted) setState(() {});
                          });
                        }
                      },
                      validator: (value) =>
                          value == null || value == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _classNameController,
                      decoration: const InputDecoration(
                        labelText: 'Class Name',
                      ),
                      validator: (value) =>
                          value == null || value.trim().isEmpty
                              ? 'Required'
                              : null,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _classNumericController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Class Number',
                      ),
                      validator: (value) =>
                          value == null || value.trim().isEmpty
                              ? 'Required'
                              : null,
                    ),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      initialValue: (_selectedTeacher != null &&
                              _teachers.any((t) => t['teacherID'].toString() == _selectedTeacher))
                          ? _selectedTeacher
                          : null,
                      decoration: const InputDecoration(labelText: 'Teacher'),
                      items: _teachers
                          .map(
                            (teacher) => DropdownMenuItem<String>(
                              value: teacher['teacherID'].toString(),
                              child: Text(teacher['name'] ?? 'Teacher'),
                            ),
                          )
                          .toList(),
                      onChanged: (value) =>
                          setState(() => _selectedTeacher = value),
                      validator: (value) =>
                          value == null || value == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _noteController,
                      maxLines: 4,
                      decoration: const InputDecoration(labelText: 'Note'),
                    ),
                    const SizedBox(height: 20),
                    ElevatedButton(onPressed: _save, child: const Text('Save')),
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
