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
    _fetchCampuses();

    if (widget.classData != null) {
      _classNameController.text = (widget.classData!['classes'] ?? '')
          .toString();
      _classNumericController.text =
          (widget.classData!['classes_numeric'] ?? '').toString();
      _noteController.text = (widget.classData!['note'] ?? '').toString();
      _selectedCampus = (widget.classData!['campusID'] ?? '').toString();
      _selectedTeacher = (widget.classData!['teacherID'] ?? '').toString();
    }
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
        setState(() {
          _campuses = result['data']['campuses'] ?? [];
        });
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
          'campusID': campusID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );

      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _teachers = result['data'] ?? [];
          if (_selectedTeacher != null &&
              _teachers.every(
                (teacher) =>
                    teacher['teacherID'].toString() != _selectedTeacher,
              )) {
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
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Please select a campus')));
      return;
    }

    if (_selectedTeacher == null || _selectedTeacher == '0') {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Please select a teacher')));
      return;
    }

    setState(() => _isLoading = true);

    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';

    final String apiUrl = widget.classData == null
        ? '${base}api/classes_add'
        : '${base}api/classes_update';

    final body = {
      'campusID': _selectedCampus,
      'classes': _classNameController.text.trim(),
      'classes_numeric': _classNumericController.text.trim(),
      'teacherID': _selectedTeacher,
      'note': _noteController.text.trim(),
      'adminID': widget.userData['adminID'] ?? 1,
      if (widget.classData != null) 'classesID': widget.classData!['classesID'],
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
      dev.log('Save class error: $e');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Connection error while saving class')),
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
                        setState(() => _selectedCampus = value);
                        if (value != null && value != '0') {
                          _fetchTeachers(value);
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
                      initialValue: _selectedTeacher,
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
}
