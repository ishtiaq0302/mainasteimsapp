import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';

import 'package:first_app/config.dart';

class StudentFormPage extends StatefulWidget {
  final Map? studentData;
  final Map userData;

  const StudentFormPage({super.key, this.studentData, required this.userData});

  @override
  State<StudentFormPage> createState() => _StudentFormPageState();
}

class _StudentFormPageState extends State<StudentFormPage> {
  final _formKey = GlobalKey<FormState>();

  // Text Controllers
  final _nameController     = TextEditingController();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _rollController     = TextEditingController();
  final _dobController      = TextEditingController();
  final _emailController    = TextEditingController();
  final _phoneController    = TextEditingController();
  final _addressController  = TextEditingController();
  final _religionController = TextEditingController();
  final _remarksController  = TextEditingController();

  // Dropdown Selections
  String? _selectedCampus;
  String? _selectedClass;
  String? _selectedSection;
  String? _selectedParent;
  String? _selectedSex;
  String? _selectedBloodGroup;

  // Metadata Lists
  List _campuses   = [];
  List _classes    = [];
  List _sections   = [];
  List _parents    = [];
  List _bloodGroups = [];

  bool _isLoadingData = false;
  bool _isSaving      = false;

  static const List<String> _genderOptions = ['Male', 'Female', 'Other'];

  String get _base {
    String b = AppConfig.baseUrl;
    return b.endsWith('/') ? b : '$b/';
  }

  @override
  void initState() {
    super.initState();
    _fetchMetadataAndStudentDetails();
  }

  Future<void> _fetchMetadataAndStudentDetails() async {
    setState(() => _isLoadingData = true);
    try {
      // 1. Fetch metadata (campuses & bloodgroups)
      final campusRes = await http.post(
        Uri.parse('${_base}api/metadata'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': 0, 'adminID': widget.userData['adminID'] ?? 1}),
      );
      final campusResult = jsonDecode(campusRes.body);
      if (campusResult['status'] == true) {
        _campuses    = campusResult['data']['campuses'] ?? [];
        _bloodGroups = campusResult['data']['bloodgroups'] ?? [];
      }

      // 2. Fetch full student details if editing
      if (widget.studentData != null) {
        final id = widget.studentData!['studentID'] ?? widget.studentData!['srstudentID'];
        if (id != null) {
          final viewRes = await http.post(
            Uri.parse('${_base}api/student_view'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({
              'studentID': id,
              'schoolyearID': widget.userData['defaultschoolyearID'] ?? 1,
            }),
          );
          final viewResult = jsonDecode(viewRes.body);
          if (viewResult['status'] == true && viewResult['data'] != null) {
            _populateFromViewData(viewResult['data']);
          } else {
            _populateFieldsForEdit(widget.studentData!);
          }
        } else {
          _populateFieldsForEdit(widget.studentData!);
        }
      }

      // 3. Fetch parents using current campus (or 0 for all parents)
      await _fetchParents(_selectedCampus ?? '0');

      // 4. Fetch classes and sections if campus & class selected
      if (_selectedCampus != null && _selectedCampus != '0') {
        await _fetchClasses(_selectedCampus!);
      }
    } catch (e) {
      dev.log('Fetch metadata error: $e', name: 'StudentForm');
    } finally {
      if (mounted) setState(() => _isLoadingData = false);
    }
  }

  void _populateFromViewData(Map d) {
    _nameController.text     = (d['name']     ?? d['srname'] ?? '').toString();
    _usernameController.text = (d['username'] ?? '').toString();
    _rollController.text     = (d['roll']     ?? d['srroll'] ?? '').toString();
    _dobController.text      = (d['dob']      ?? '').toString();
    _emailController.text    = (d['email']    ?? '').toString();
    _phoneController.text    = (d['phone']    ?? '').toString();
    _addressController.text  = (d['address']  ?? '').toString();
    _religionController.text = (d['religion'] ?? '').toString();
    _remarksController.text  = (d['remarks']  ?? '').toString();

    final sex = (d['sex'] ?? '').toString();
    if (_genderOptions.contains(sex)) _selectedSex = sex;

    final bg = (d['bloodgroup'] ?? '').toString();
    if (bg.isNotEmpty) _selectedBloodGroup = bg;

    _selectedCampus  = (d['campusID'] ?? d['srcampusID'])?.toString();
    _selectedClass   = (d['classesID'] ?? d['srclassesID'])?.toString();
    _selectedSection = (d['sectionID'] ?? d['srsectionID'])?.toString();
    _selectedParent  = d['parentID']?.toString();
  }

  void _populateFieldsForEdit(Map d) {
    _nameController.text     = (d['name']     ?? d['srname'] ?? '').toString();
    _usernameController.text = (d['username'] ?? '').toString();
    _rollController.text     = (d['roll']     ?? d['srroll'] ?? '').toString();
    _dobController.text      = (d['dob']      ?? '').toString();
    _emailController.text    = (d['email']    ?? '').toString();
    _phoneController.text    = (d['phone']    ?? '').toString();
    _addressController.text  = (d['address']  ?? '').toString();
    _religionController.text = (d['religion'] ?? '').toString();
    _remarksController.text  = (d['remarks']  ?? '').toString();

    final sex = (d['sex'] ?? '').toString();
    if (_genderOptions.contains(sex)) _selectedSex = sex;

    _selectedCampus  = (d['campusID'] ?? d['srcampusID'])?.toString();
    _selectedClass   = (d['classesID'] ?? d['srclassesID'])?.toString();
    _selectedSection = (d['sectionID'] ?? d['srsectionID'])?.toString();
    _selectedParent  = d['parentID']?.toString();
  }

  Future<void> _fetchParents(String campusID) async {
    try {
      final parentRes = await http.post(
        Uri.parse('${_base}api/parents'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID': campusID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final parentResult = jsonDecode(parentRes.body);
      if (parentResult['status'] == true) {
        setState(() {
          _parents = parentResult['data'] ?? [];
        });
      }
    } catch (e) {
      dev.log('Fetch parents error: $e', name: 'StudentForm');
    }
  }


  Future<void> _fetchClasses(String campusID) async {
    try {
      final res = await http.post(
        Uri.parse('${_base}api/classes'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': campusID, 'adminID': widget.userData['adminID'] ?? 1}),
      );
      final result = jsonDecode(res.body);
      if (result['status'] == true) {
        setState(() {
          _classes = result['data'] ?? [];
          if (_selectedClass != null &&
              _classes.every((c) => c['classesID'].toString() != _selectedClass)) {
            _selectedClass   = null;
            _selectedSection = null;
            _sections        = [];
          }
        });
        if (_selectedClass != null && _selectedClass != '0') {
          await _fetchSections(_selectedClass!);
        }
      }
    } catch (e) {
      dev.log('Fetch classes error: $e', name: 'StudentForm');
    }
  }

  Future<void> _fetchSections(String classesID) async {
    try {
      final res = await http.post(
        Uri.parse('${_base}api/section'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'classesID': classesID,
          'campusID':  _selectedCampus ?? '0',
          'adminID':   widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(res.body);
      if (result['status'] == true) {
        setState(() {
          _sections = result['data'] ?? [];
          if (_selectedSection != null &&
              _sections.every((s) => s['sectionID'].toString() != _selectedSection)) {
            _selectedSection = null;
          }
        });
      }
    } catch (e) {
      dev.log('Fetch sections error: $e', name: 'StudentForm');
    }
  }

  // ── Date Picker ─────────────────────────────────────────────────────
  Future<void> _pickDob() async {
    final now     = DateTime.now();
    final initial = DateTime(now.year - 10, now.month, now.day);
    final picked  = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(1970),
      lastDate: now,
    );
    if (picked != null) {
      setState(() {
        _dobController.text = DateFormat('yyyy-MM-dd').format(picked);
      });
    }
  }

  // ── Save ───────────────────────────────────────────────────────────
  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    if (_selectedCampus == null || _selectedCampus == '0') {
      _snack('Please select a campus'); return;
    }
    if (_selectedClass == null || _selectedClass == '0') {
      _snack('Please select a class'); return;
    }
    if (_selectedSection == null || _selectedSection == '0') {
      _snack('Please select a section'); return;
    }

    setState(() => _isSaving = true);

    final isEdit  = widget.studentData != null;
    final apiUrl  = isEdit
        ? '${_base}api/student_update'
        : '${_base}api/student_add';

    var body = <String, dynamic>{
      'name':          _nameController.text.trim(),
      'username':      _usernameController.text.trim(),
      'roll':          _rollController.text.trim(),
      'dob':           _dobController.text.trim(),
      'sex':           _selectedSex ?? '',
      'bloodgroup':    _selectedBloodGroup ?? '',
      'email':         _emailController.text.trim(),
      'phone':         _phoneController.text.trim(),
      'address':       _addressController.text.trim(),
      'religion':      _religionController.text.trim(),
      'remarks':       _remarksController.text.trim(),
      'campusID':      _selectedCampus,
      'classesID':     _selectedClass,
      'sectionID':     _selectedSection,
      'parentID':      _selectedParent ?? '',
      'schoolyearID':  widget.userData['defaultschoolyearID'] ?? 1,
      
      'adminID': widget.userData['adminID'] ?? 1,
      'create_userID': _getCreateUserID(),
      'create_username': widget.userData['username'] ?? widget.userData['name'] ?? 'admin',
      'create_usertype': widget.userData['user_type'] ?? 'Admin',
    };

    if (!isEdit) {
      body['password'] = _passwordController.text.trim();
    }
    if (isEdit) {
      body['studentID'] = widget.studentData!['studentID'] ?? widget.studentData!['srstudentID'];
    }

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );

      Map result;
      try {
        result = jsonDecode(response.body);
      } catch (e) {
        result = {'status': false, 'message': response.body};
      }

      if (!mounted) return;

      if (result['status'] == true) {
        Navigator.pop(context, true);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Saved successfully')),
        );
      } else {
        _snack(result['message'] ?? 'Save failed — check all required fields');
      }
    } catch (e) {
      dev.log('Save student error: $e', name: 'StudentForm');
      if (!mounted) return;
      _snack('Error saving student: $e');
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  void _snack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.studentData != null;

    return Scaffold(
      appBar: AppBar(
        title: Text(isEdit ? 'Edit Student' : 'Add Student'),
      ),
      body: _isLoadingData
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: ListView(
                  children: [

                    // ── Campus ─────────────────────────────────────
                    DropdownButtonFormField<String>(
                      value: _selectedCampus,
                      decoration: const InputDecoration(labelText: 'Campus *'),
                      items: _campuses.map((c) => DropdownMenuItem<String>(
                        value: c['campusID'].toString(),
                        child: Text(c['name'] ?? 'Campus'),
                      )).toList(),
                      onChanged: (v) {
                        setState(() {
                          _selectedCampus  = v;
                          _selectedClass   = null;
                          _selectedSection = null;
                          _classes         = [];
                          _sections        = [];
                        });
                        if (v != null) {
                          _fetchClasses(v);
                          _fetchParents(v);
                        }
                      },
                      validator: (v) => v == null || v == '0' ? 'Campus is required' : null,
                    ),
                    const SizedBox(height: 12),

                    // ── Class ──────────────────────────────────────
                    DropdownButtonFormField<String>(
                      value: _selectedClass,
                      decoration: const InputDecoration(labelText: 'Class *'),
                      items: _classes.map((c) => DropdownMenuItem<String>(
                        value: c['classesID'].toString(),
                        child: Text(c['classes'] ?? 'Class'),
                      )).toList(),
                      onChanged: (v) {
                        setState(() {
                          _selectedClass   = v;
                          _selectedSection = null;
                          _sections        = [];
                        });
                        if (v != null) _fetchSections(v);
                      },
                      validator: (v) => v == null || v == '0' ? 'Class is required' : null,
                    ),
                    const SizedBox(height: 12),

                    // ── Section ────────────────────────────────────
                    DropdownButtonFormField<String>(
                      value: _selectedSection,
                      decoration: const InputDecoration(labelText: 'Section *'),
                      items: _sections.map((s) => DropdownMenuItem<String>(
                        value: s['sectionID'].toString(),
                        child: Text(s['section'] ?? 'Section'),
                      )).toList(),
                      onChanged: (v) => setState(() => _selectedSection = v),
                      validator: (v) => v == null || v == '0' ? 'Section is required' : null,
                    ),
                    const SizedBox(height: 12),

                    // ── Name ───────────────────────────────────────
                    TextFormField(
                      controller: _nameController,
                      decoration: const InputDecoration(labelText: 'Full Name *'),
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Name is required' : null,
                    ),
                    const SizedBox(height: 12),

                    // ── Username ───────────────────────────────────
                    TextFormField(
                      controller: _usernameController,
                      decoration: const InputDecoration(labelText: 'Username *'),
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Username is required' : null,
                    ),
                    const SizedBox(height: 12),

                    // ── Password (Add mode only) ───────────────────
                    if (!isEdit) ...[
                      TextFormField(
                        controller: _passwordController,
                        obscureText: true,
                        decoration: const InputDecoration(labelText: 'Password *'),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Password is required' : null,
                      ),
                      const SizedBox(height: 12),
                    ],

                    // ── Roll ───────────────────────────────────────
                    TextFormField(
                      controller: _rollController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Roll No. *'),
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Roll is required' : null,
                    ),
                    const SizedBox(height: 12),

                    // ── Date of Birth ──────────────────────────────
                    TextFormField(
                      controller: _dobController,
                      readOnly: true,
                      decoration: InputDecoration(
                        labelText: 'Date of Birth',
                        suffixIcon: IconButton(
                          icon: const Icon(Icons.calendar_today),
                          onPressed: _pickDob,
                        ),
                      ),
                      onTap: _pickDob,
                    ),
                    const SizedBox(height: 12),

                    // ── Gender ─────────────────────────────────────
                    DropdownButtonFormField<String>(
                      value: _selectedSex,
                      decoration: const InputDecoration(labelText: 'Gender'),
                      items: _genderOptions.map((g) => DropdownMenuItem<String>(
                        value: g, child: Text(g),
                      )).toList(),
                      onChanged: (v) => setState(() => _selectedSex = v),
                    ),
                    const SizedBox(height: 12),

                    // ── Blood Group ────────────────────────────────
                    if (_bloodGroups.isNotEmpty) ...[
                      DropdownButtonFormField<String>(
                        value: _selectedBloodGroup,
                        decoration: const InputDecoration(labelText: 'Blood Group'),
                        items: _bloodGroups.map((b) => DropdownMenuItem<String>(
                          value: b['bloodgroup'].toString(),
                          child: Text(b['bloodgroup'].toString()),
                        )).toList(),
                        onChanged: (v) => setState(() => _selectedBloodGroup = v),
                      ),
                      const SizedBox(height: 12),
                    ],

                    // ── Parent / Guardian ──────────────────────────
                    DropdownButtonFormField<String>(
                      value: _selectedParent,
                      decoration: const InputDecoration(labelText: 'Parent / Guardian (Optional)'),
                      items: [
                        const DropdownMenuItem<String>(value: null, child: Text('None')),
                        ..._parents.map((p) => DropdownMenuItem<String>(
                          value: p['parentsID'].toString(),
                          child: Text('${p['name']} (${p['phone'] ?? ''})'),
                        )),
                      ],
                      onChanged: (v) => setState(() => _selectedParent = v),
                    ),
                    const SizedBox(height: 12),

                    // ── Email ──────────────────────────────────────
                    TextFormField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      decoration: const InputDecoration(labelText: 'Email'),
                    ),
                    const SizedBox(height: 12),

                    // ── Phone ──────────────────────────────────────
                    TextFormField(
                      controller: _phoneController,
                      keyboardType: TextInputType.phone,
                      decoration: const InputDecoration(labelText: 'Phone'),
                    ),
                    const SizedBox(height: 12),

                    // ── Religion ───────────────────────────────────
                    TextFormField(
                      controller: _religionController,
                      decoration: const InputDecoration(labelText: 'Religion'),
                    ),
                    const SizedBox(height: 12),

                    // ── Address ────────────────────────────────────
                    TextFormField(
                      controller: _addressController,
                      maxLines: 2,
                      decoration: const InputDecoration(labelText: 'Address'),
                    ),
                    const SizedBox(height: 12),

                    // ── Remarks ────────────────────────────────────
                    TextFormField(
                      controller: _remarksController,
                      maxLines: 2,
                      decoration: const InputDecoration(labelText: 'Remarks'),
                    ),
                    const SizedBox(height: 24),

                    // ── Submit Button ──────────────────────────────
                    ElevatedButton(
                      onPressed: _isSaving ? null : _save,
                      child: _isSaving
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : Text(isEdit ? 'Update Student' : 'Add Student'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  int _getCreateUserID() {
    final d = widget.userData;
    final keys = ['systemadminID', 'userID', 'teacherID', 'parentsID', 'studentID', 'create_userID', 'adminID'];
    for (var k in keys) {
      if (d[k] != null && d[k].toString() != '0') return int.tryParse(d[k].toString()) ?? 1;
    }
    return 1;
  }
}
