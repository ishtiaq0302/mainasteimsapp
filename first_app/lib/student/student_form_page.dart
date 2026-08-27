import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

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

  // Required fields
  final _nameController     = TextEditingController();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _rollController     = TextEditingController();

  // Optional fields matching MVC controller rules
  final _dobController      = TextEditingController();
  final _emailController    = TextEditingController();
  final _phoneController    = TextEditingController();
  final _addressController  = TextEditingController();
  final _religionController = TextEditingController();
  final _remarksController  = TextEditingController();

  String? _selectedSex;
  String? _selectedBloodGroup;
  String? _selectedCampus;
  String? _selectedClass;
  String? _selectedSection;
  String? _selectedParent;

  List _campuses = [];
  List _classes  = [];
  List _sections = [];
  List _parents  = [];

  bool _isLoadingMeta  = false;
  bool _isSaving       = false;
  bool _obscurePassword = true;

  final List<String> _sexOptions = ['Male', 'Female', 'Other'];
  final List<String> _bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

  // ── Lifecycle ─────────────────────────────────────────────────────
  @override
  void initState() {
    super.initState();
    _fetchMeta();

    if (widget.studentData != null) {
      final d = widget.studentData!;
      _nameController.text     = (d['name']     ?? d['srname'] ?? '').toString();
      _usernameController.text = (d['username']  ?? '').toString();
      _rollController.text     = (d['roll']      ?? d['srroll'] ?? '').toString();
      _dobController.text      = (d['dob']       ?? '').toString();
      _emailController.text    = (d['email']     ?? '').toString();
      _phoneController.text    = (d['phone']     ?? '').toString();
      _addressController.text  = (d['address']   ?? '').toString();
      _religionController.text = (d['religion']  ?? '').toString();
      _remarksController.text  = (d['remarks']   ?? '').toString();
      _selectedSex        = (d['sex']       ?? '').toString().isEmpty ? null : d['sex'].toString();
      _selectedBloodGroup = (d['bloodgroup'] ?? '').toString().isEmpty ? null : d['bloodgroup'].toString();
      _selectedCampus     = (d['campusID']   ?? d['srcampusID'] ?? '').toString();
      _selectedClass      = (d['classesID']  ?? d['srclassesID'] ?? '').toString();
      _selectedSection    = (d['sectionID']  ?? d['srsectionID'] ?? '').toString();
      _selectedParent     = (d['parentID']   ?? '').toString();
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _usernameController.dispose();
    _passwordController.dispose();
    _rollController.dispose();
    _dobController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _religionController.dispose();
    _remarksController.dispose();
    super.dispose();
  }

  // ── Fetch helpers ──────────────────────────────────────────────────
  String get _base {
    String b = AppConfig.baseUrl;
    if (!b.endsWith('/')) b += '/';
    return b;
  }

  Future<void> _fetchMeta() async {
    setState(() => _isLoadingMeta = true);
    try {
      final res = await http.post(
        Uri.parse('${_base}api/metadata'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID': widget.userData['campusID'] ?? 0,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(res.body);
      if (result['status'] == true) {
        setState(() {
          _campuses = result['data']['campuses'] ?? [];
          _classes  = result['data']['classes']  ?? [];
          _parents  = result['data']['parents']  ?? [];
        });
      }
      // If editing, load sections for the pre-selected class
      if (_selectedClass != null && _selectedClass != '0') {
        await _fetchSections(_selectedClass!);
      }
    } catch (e) {
      dev.log('Fetch meta error: $e');
    } finally {
      if (mounted) setState(() => _isLoadingMeta = false);
    }
  }

  Future<void> _fetchClasses(String campusID) async {
    try {
      final res = await http.post(
        Uri.parse('${_base}api/classes'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID': campusID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(res.body);
      if (result['status'] == true) {
        setState(() {
          _classes         = result['data'] ?? [];
          _selectedClass   = null;
          _sections        = [];
          _selectedSection = null;
        });
      }
    } catch (e) {
      dev.log('Fetch classes error: $e');
    }
  }

  Future<void> _fetchSections(String classesID) async {
    try {
      final res = await http.post(
        Uri.parse('${_base}api/section'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'classesID': classesID,
          'campusID': _selectedCampus ?? 0,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(res.body);
      if (result['status'] == true) {
        setState(() {
          _sections        = result['data'] ?? [];
          _selectedSection = null;
        });
      }
    } catch (e) {
      dev.log('Fetch sections error: $e');
    }
  }

  // ── Save ───────────────────────────────────────────────────────────
  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    // Extra guards
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

    final body = <String, dynamic>{
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
      'adminID':       widget.userData['adminID'] ?? 1,
      'schoolyearID':  widget.userData['defaultschoolyearID'] ?? 1,
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
      final result = jsonDecode(response.body);
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
      dev.log('Save student error: $e');
      if (!mounted) return;
      _snack('Connection error. Please check your network and try again.');
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  void _snack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  // ── Widgets ────────────────────────────────────────────────────────
  Widget _field(
    TextEditingController ctrl,
    String label, {
    TextInputType keyboard = TextInputType.text,
    bool required = false,
    bool obscure = false,
    Widget? suffix,
    int maxLines = 1,
    String? hint,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextFormField(
        controller: ctrl,
        obscureText: obscure,
        keyboardType: keyboard,
        maxLines: maxLines,
        decoration: InputDecoration(
          labelText: label + (required ? ' *' : ''),
          hintText: hint,
          border: const OutlineInputBorder(),
          isDense: true,
          suffixIcon: suffix,
        ),
        validator: required
            ? (v) => v == null || v.trim().isEmpty ? '$label is required' : null
            : null,
      ),
    );
  }

  Widget _dropdown<T>(
    String label,
    T? value,
    List<DropdownMenuItem<T>> items,
    void Function(T?) onChanged, {
    bool required = false,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: DropdownButtonFormField<T>(
        value: value,
        decoration: InputDecoration(
          labelText: label + (required ? ' *' : ''),
          border: const OutlineInputBorder(),
          isDense: true,
        ),
        items: items,
        onChanged: onChanged,
        validator: required
            ? (v) => v == null ? '$label is required' : null
            : null,
      ),
    );
  }

  Widget _sectionHeader(String title) => Padding(
        padding: const EdgeInsets.only(top: 16, bottom: 8),
        child: Text(
          title,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Colors.blueGrey),
        ),
      );

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.studentData != null;

    return Scaffold(
      appBar: AppBar(title: Text(isEdit ? 'Edit Student' : 'Add Student')),
      body: _isLoadingMeta
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: ListView(
                  children: [
                    // ── Academic placement ───────────────────────────
                    _sectionHeader('Academic Placement'),

                    _dropdown<String>(
                      'Campus', _selectedCampus,
                      _campuses.map((c) => DropdownMenuItem<String>(
                        value: c['campusID'].toString(),
                        child: Text(c['name'] ?? 'Campus'),
                      )).toList(),
                      (v) {
                        setState(() {
                          _selectedCampus  = v;
                          _selectedClass   = null;
                          _selectedSection = null;
                          _classes  = [];
                          _sections = [];
                        });
                        if (v != null) _fetchClasses(v);
                      },
                      required: true,
                    ),

                    _dropdown<String>(
                      'Class', _selectedClass,
                      _classes.map((c) => DropdownMenuItem<String>(
                        value: c['classesID'].toString(),
                        child: Text(c['classes'] ?? 'Class'),
                      )).toList(),
                      (v) {
                        setState(() { _selectedClass = v; _selectedSection = null; _sections = []; });
                        if (v != null) _fetchSections(v);
                      },
                      required: true,
                    ),

                    _dropdown<String>(
                      'Section', _selectedSection,
                      _sections.map((s) => DropdownMenuItem<String>(
                        value: s['sectionID'].toString(),
                        child: Text(s['section'] ?? 'Section'),
                      )).toList(),
                      (v) => setState(() => _selectedSection = v),
                      required: true,
                    ),

                    // ── Personal info ────────────────────────────────
                    _sectionHeader('Personal Information'),

                    _field(_nameController, 'Full Name', required: true),

                    _dropdown<String>(
                      'Gender', _selectedSex,
                      _sexOptions.map((s) => DropdownMenuItem(value: s, child: Text(s))).toList(),
                      (v) => setState(() => _selectedSex = v),
                      required: true,
                    ),

                    _field(_dobController, 'Date of Birth',
                        keyboard: TextInputType.datetime,
                        hint: 'YYYY-MM-DD'),

                    _dropdown<String>(
                      'Blood Group', _selectedBloodGroup,
                      _bloodGroups.map((b) => DropdownMenuItem(value: b, child: Text(b))).toList(),
                      (v) => setState(() => _selectedBloodGroup = v),
                    ),

                    _field(_religionController, 'Religion'),

                    _field(_emailController, 'Email',
                        keyboard: TextInputType.emailAddress),

                    _field(_phoneController, 'Phone',
                        keyboard: TextInputType.phone),

                    _field(_addressController, 'Address', maxLines: 2),

                    // ── School info ──────────────────────────────────
                    _sectionHeader('School Information'),

                    _field(_rollController, 'Roll No',
                        keyboard: TextInputType.number),

                    _dropdown<String>(
                      'Parent / Guardian', _selectedParent,
                      _parents.map((p) => DropdownMenuItem<String>(
                        value: p['parentsID'].toString(),
                        child: Text(p['name'] ?? 'Parent'),
                      )).toList(),
                      (v) => setState(() => _selectedParent = v),
                    ),

                    _field(_remarksController, 'Remarks', maxLines: 2),

                    // ── Account credentials ──────────────────────────
                    _sectionHeader('Login Credentials'),

                    _field(_usernameController, 'Username', required: true),

                    if (!isEdit)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: TextFormField(
                          controller: _passwordController,
                          obscureText: _obscurePassword,
                          decoration: InputDecoration(
                            labelText: 'Password *',
                            border: const OutlineInputBorder(),
                            isDense: true,
                            suffixIcon: IconButton(
                              icon: Icon(_obscurePassword
                                  ? Icons.visibility_off
                                  : Icons.visibility),
                              onPressed: () => setState(
                                  () => _obscurePassword = !_obscurePassword),
                            ),
                          ),
                          validator: (v) {
                            if (!isEdit && (v == null || v.trim().length < 4)) {
                              return 'Password must be at least 4 characters';
                            }
                            return null;
                          },
                        ),
                      ),

                    const SizedBox(height: 20),

                    ElevatedButton.icon(
                      onPressed: _isSaving ? null : _save,
                      icon: _isSaving
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(
                                  strokeWidth: 2, color: Colors.white),
                            )
                          : const Icon(Icons.save),
                      label: Text(isEdit ? 'Update Student' : 'Add Student'),
                      style: ElevatedButton.styleFrom(
                        minimumSize: const Size.fromHeight(48),
                      ),
                    ),

                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ),
    );
  }
}
