import 'dart:convert';
import 'dart:developer' as dev;

import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';

import 'package:first_app/config.dart';

class AssignmentFormPage extends StatefulWidget {
  final Map? assignmentData;
  final Map userData;
  final String? preselectedCampusID;
  final String? preselectedClassesID;

  const AssignmentFormPage({
    super.key,
    this.assignmentData,
    required this.userData,
    this.preselectedCampusID,
    this.preselectedClassesID,
  });

  @override
  State<AssignmentFormPage> createState() => _AssignmentFormPageState();
}

class _AssignmentFormPageState extends State<AssignmentFormPage> {
  final _formKey      = GlobalKey<FormState>();
  final _titleCtrl    = TextEditingController();
  final _descCtrl     = TextEditingController();
  final _deadlineCtrl = TextEditingController();

  List _campuses = [];
  List _classes  = [];
  List _subjects = [];
  List _sections = [];

  String? _selectedCampus;
  String? _selectedClass;
  String? _selectedSubject;
  List<String> _selectedSectionIDs = [];
  bool _isLoading = false;

  PlatformFile? _pickedFile;

  @override
  void initState() {
    super.initState();
    _fetchCampuses();

    if (widget.assignmentData != null) {
      final d = widget.assignmentData!;
      _titleCtrl.text    = (d['title']        ?? '').toString();
      _descCtrl.text     = (d['description']  ?? '').toString();
      _deadlineCtrl.text = (d['deadlinedate'] ?? '').toString();
      _selectedCampus    = (d['campusID']      ?? '').toString();
      _selectedClass     = (d['classesID']     ?? '').toString();
      _selectedSubject   = (d['subjectID']     ?? '').toString();

      if (d['sectionIDs'] != null && d['sectionIDs'] is List) {
        _selectedSectionIDs = (d['sectionIDs'] as List).map((e) => e.toString()).toList();
      } else if (d['sectionID'] != null) {
        try {
          final decoded = jsonDecode(d['sectionID'].toString());
          if (decoded is List) {
            _selectedSectionIDs = decoded.map((e) => e.toString()).toList();
          }
        } catch (_) {}
      }
    } else {
      _selectedCampus = widget.preselectedCampusID;
      _selectedClass  = widget.preselectedClassesID;
    }
  }

  @override
  void dispose() {
    _titleCtrl.dispose();
    _descCtrl.dispose();
    _deadlineCtrl.dispose();
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
        if (_selectedClass != null) {
          await _fetchSubjects(_selectedClass!);
          await _fetchSections(_selectedClass!);
        }
      }
    } catch (e) {
      dev.log('Fetch classes error: $e');
    }
  }

  Future<void> _fetchSubjects(String classesID) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/subject'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'classesID': classesID,
          'campusID':  _selectedCampus ?? '0',
          'adminID':   widget.userData['adminID'] ?? 1,

      'create_userID': _getCreateUserID(),
      'create_username': widget.userData['username'] ?? widget.userData['name'] ?? 'admin',
      'create_usertype': widget.userData['user_type'] ?? 'Admin',

        }),
      );
      final result = jsonDecode(res.body);
      if (result['status'] == true) {
        setState(() {
          _subjects = result['data'] ?? [];
          if (_selectedSubject != null &&
              _subjects.every((s) => s['subjectID'].toString() != _selectedSubject)) {
            _selectedSubject = null;
          }
        });
      }
    } catch (e) {
      dev.log('Fetch subjects error: $e');
    }
  }

  Future<void> _fetchSections(String classesID) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/section'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'classesID': classesID,
          'campusID':  _selectedCampus ?? '0',
          'adminID':   widget.userData['adminID'] ?? 1,

      'create_userID': _getCreateUserID(),
      'create_username': widget.userData['username'] ?? widget.userData['name'] ?? 'admin',
      'create_usertype': widget.userData['user_type'] ?? 'Admin',

        }),
      );
      final result = jsonDecode(res.body);
      if (result['status'] == true) {
        setState(() {
          _sections = result['data'] ?? [];
        });
      }
    } catch (e) {
      dev.log('Fetch sections error: $e');
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

  Future<void> _pickDeadline() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: now,
      firstDate: now,
      lastDate: DateTime(now.year + 2),
    );
    if (picked != null) {
      setState(() {
        _deadlineCtrl.text = DateFormat('yyyy-MM-dd').format(picked);
      });
    }
  }

  void _showSectionMultiSelectDialog() {
    showDialog(
      context: context,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: const Text('Select Sections'),
              content: SizedBox(
                width: double.maxFinite,
                child: _sections.isEmpty
                    ? const Padding(
                        padding: EdgeInsets.all(16.0),
                        child: Text('No sections available for this class.'),
                      )
                    : ListView.builder(
                        shrinkWrap: true,
                        itemCount: _sections.length,
                        itemBuilder: (context, index) {
                          final sec = _sections[index];
                          final secID = sec['sectionID'].toString();
                          final secName = sec['section'] ?? 'Section';
                          final isChecked = _selectedSectionIDs.contains(secID);

                          return CheckboxListTile(
                            title: Text(secName),
                            value: isChecked,
                            onChanged: (bool? checked) {
                              setDialogState(() {
                                if (checked == true) {
                                  _selectedSectionIDs.add(secID);
                                } else {
                                  _selectedSectionIDs.remove(secID);
                                }
                              });
                              setState(() {});
                            },
                          );
                        },
                      ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Done'),
                ),
              ],
            );
          },
        );
      },
    );
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
    if (_selectedSubject == null || _selectedSubject == '0') {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please select a subject')));
      return;
    }

    setState(() => _isLoading = true);

    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';

    final isEdit = widget.assignmentData != null;
    final apiUrl = isEdit ? '${base}api/assignment_update' : '${base}api/assignment_add';

    try {
      final request = http.MultipartRequest('POST', Uri.parse(apiUrl));

      final rawFields = <String, dynamic>{
        'campusID':     _selectedCampus!,
        'classesID':    _selectedClass!,
        'subjectID':    _selectedSubject!,
        'title':        _titleCtrl.text.trim(),
        'description':  _descCtrl.text.trim(),
        'deadlinedate': _deadlineCtrl.text.trim(),
        'schoolyearID': (widget.userData['defaultschoolyearID'] ?? 0).toString(),
        'sectionIDs':   jsonEncode(_selectedSectionIDs),
        if (isEdit) 'assignmentID': widget.assignmentData!['assignmentID'].toString(),
        
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
      dev.log('Save assignment error: $e');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error saving assignment: $e')),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final existingFile = widget.assignmentData?['originalfile'] ?? widget.assignmentData?['file'];

    // Construct summary of selected sections
    String sectionText = 'Select Sections (Optional)';
    if (_selectedSectionIDs.isNotEmpty && _sections.isNotEmpty) {
      final names = _sections
          .where((s) => _selectedSectionIDs.contains(s['sectionID'].toString()))
          .map((s) => s['section'].toString())
          .toList();
      if (names.isNotEmpty) {
        sectionText = names.join(', ');
      }
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.assignmentData == null ? 'Add Assignment' : 'Edit Assignment'),
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
                          _selectedCampus     = value;
                          _selectedClass      = null;
                          _selectedSubject    = null;
                          _selectedSectionIDs = [];
                          _classes  = [];
                          _subjects = [];
                          _sections = [];
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
                      onChanged: (value) {
                        setState(() {
                          _selectedClass      = value;
                          _selectedSubject    = null;
                          _selectedSectionIDs = [];
                          _subjects = [];
                          _sections = [];
                        });
                        if (value != null) {
                          _fetchSubjects(value);
                          _fetchSections(value);
                        }
                      },
                      validator: (v) => v == null || v == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Multi-select Section
                    InkWell(
                      onTap: _selectedClass != null ? _showSectionMultiSelectDialog : null,
                      child: InputDecorator(
                        decoration: const InputDecoration(
                          labelText: 'Sections (Optional)',
                          border: OutlineInputBorder(),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Text(
                                sectionText,
                                style: const TextStyle(fontSize: 14),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            const Icon(Icons.arrow_drop_down),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Subject
                    DropdownButtonFormField<String>(
                      value: _selectedSubject,
                      decoration: const InputDecoration(labelText: 'Subject'),
                      items: _subjects.map((s) => DropdownMenuItem<String>(
                            value: s['subjectID'].toString(),
                            child: Text(s['subject'] ?? 'Subject'),
                          )).toList(),
                      onChanged: (value) => setState(() => _selectedSubject = value),
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
                    const SizedBox(height: 12),

                    // Deadline date
                    TextFormField(
                      controller: _deadlineCtrl,
                      readOnly: true,
                      decoration: InputDecoration(
                        labelText: 'Deadline Date',
                        suffixIcon: IconButton(
                          icon: const Icon(Icons.calendar_today),
                          onPressed: _pickDeadline,
                        ),
                      ),
                      onTap: _pickDeadline,
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
                      child: Text(widget.assignmentData == null ? 'Add Assignment' : 'Update Assignment'),
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
