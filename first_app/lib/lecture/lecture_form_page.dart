import 'dart:convert';
import 'dart:developer' as dev;
import 'dart:io';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class LectureFormPage extends StatefulWidget {
  final Map? lectureData;
  final Map userData;
  final String? preselectedCampusID;
  final String? preselectedClassesID;

  const LectureFormPage({
    super.key,
    this.lectureData,
    required this.userData,
    this.preselectedCampusID,
    this.preselectedClassesID,
  });

  @override
  State<LectureFormPage> createState() => _LectureFormPageState();
}

class _LectureFormPageState extends State<LectureFormPage> {
  final _formKey   = GlobalKey<FormState>();
  final _titleCtrl = TextEditingController();
  final _descCtrl  = TextEditingController();

  List _campuses = [];
  List _classes  = [];

  String? _selectedCampus;
  String? _selectedClass;
  bool _isLoading = false;

  PlatformFile? _pickedFile;

  @override
  void initState() {
    super.initState();
    _fetchCampuses();

    if (widget.lectureData != null) {
      final d = widget.lectureData!;
      _titleCtrl.text = (d['title']       ?? '').toString();
      _descCtrl.text  = (d['description'] ?? '').toString();
      _selectedCampus = (d['campusID']    ?? '').toString();
      _selectedClass  = (d['classesID']   ?? '').toString();
    } else {
      _selectedCampus = widget.preselectedCampusID;
      _selectedClass  = widget.preselectedClassesID;
    }
  }

  @override
  void dispose() {
    _titleCtrl.dispose();
    _descCtrl.dispose();
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
        if (_selectedCampus != null) await _fetchClasses(_selectedCampus!);
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

  Future<void> _pickFile() async {
    try {
      final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png'],
      );

      if (result != null && result.files.isNotEmpty) {
        setState(() {
          _pickedFile = result.files.first;
        });
      }
    } catch (e) {
      dev.log('File pick error: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('File picker error: $e')),
        );
      }
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

    setState(() => _isLoading = true);

    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';

    final isEdit = widget.lectureData != null;
    final apiUrl = isEdit ? '${base}api/lecture_update' : '${base}api/lecture_add';

    try {
      final request = http.MultipartRequest('POST', Uri.parse(apiUrl));

      final rawFields = <String, dynamic>{
        'campusID': _selectedCampus!,
        'classesID': _selectedClass!,
        'title': _titleCtrl.text.trim(),
        'description': _descCtrl.text.trim(),
        'schoolyearID': (widget.userData['defaultschoolyearID'] ?? 0).toString(),
        if (isEdit) 'lectureID': widget.lectureData!['lectureID'].toString(),

        'adminID': widget.userData['adminID'] ?? 1,
        'create_userID': _getCreateUserID(),
        'create_username': widget.userData['username'] ?? widget.userData['name'] ?? 'admin',
        'create_usertype': widget.userData['user_type'] ?? 'Admin',
      };
      
      rawFields.forEach((key, val) {
        request.fields[key] = val.toString();
      });

      if (_pickedFile != null) {
        if (kIsWeb) {
          if (_pickedFile!.bytes != null) {
            request.files.add(
              http.MultipartFile.fromBytes(
                'file',
                _pickedFile!.bytes!,
                filename: _pickedFile!.name,
              ),
            );
          }
        } else {
          if (_pickedFile!.path != null) {
            request.files.add(
              await http.MultipartFile.fromPath(
                'file',
                _pickedFile!.path!,
                filename: _pickedFile!.name,
              ),
            );
          }
        }
      }

      final streamedRes = await request.send();
      final response = await http.Response.fromStream(streamedRes);

      Map result;
      try {
        result = jsonDecode(response.body);
      } catch (_) {
        result = {'status': false, 'message': response.body};
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
      dev.log('Save lecture error: $e');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error saving lecture: $e')),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final existingFile = widget.lectureData?['originalfile'] ?? widget.lectureData?['file'];

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.lectureData == null ? 'Add Lecture' : 'Edit Lecture'),
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
                          _selectedCampus = value;
                          _selectedClass  = null;
                          _classes = [];
                        });
                        if (value != null) _fetchClasses(value);
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

                    // Title
                    TextFormField(
                      controller: _titleCtrl,
                      decoration: const InputDecoration(labelText: 'Title'),
                      validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Description
                    TextFormField(
                      controller: _descCtrl,
                      maxLines: 4,
                      decoration: const InputDecoration(labelText: 'Description'),
                      validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
                    ),
                    const SizedBox(height: 16),

                    // File upload field
                    InputDecorator(
                      decoration: const InputDecoration(
                        labelText: 'Attachment / Document File (Optional)',
                        border: OutlineInputBorder(),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              _pickedFile != null
                                  ? _pickedFile!.name
                                  : (existingFile != null && existingFile.toString().isNotEmpty)
                                      ? 'Existing: $existingFile'
                                      : 'No file selected',
                              style: const TextStyle(fontSize: 14),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          ElevatedButton.icon(
                            onPressed: _pickFile,
                            icon: const Icon(Icons.attach_file, size: 18),
                            label: Text(_pickedFile != null ? 'Change' : 'Browse'),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    ElevatedButton(
                      onPressed: _save,
                      child: Text(widget.lectureData == null ? 'Add Lecture' : 'Update Lecture'),
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
