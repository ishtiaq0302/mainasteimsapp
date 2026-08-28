import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

/// Add or edit a class routine entry.
/// All cascading dropdowns: Campus → Class → Section + Subject + Teacher + School Year
class RoutineFormPage extends StatefulWidget {
  final Map?    routineData;            // non-null = edit mode
  final Map     userData;
  final String? preselectedCampusID;
  final String? preselectedClassesID;
  final String? preselectedSectionID;
  final String? preselectedSchoolyearID;

  const RoutineFormPage({
    super.key,
    this.routineData,
    required this.userData,
    this.preselectedCampusID,
    this.preselectedClassesID,
    this.preselectedSectionID,
    this.preselectedSchoolyearID,
  });

  @override
  State<RoutineFormPage> createState() => _RoutineFormPageState();
}

class _RoutineFormPageState extends State<RoutineFormPage> {
  final _formKey     = GlobalKey<FormState>();
  final _roomCtrl    = TextEditingController();

  // Dropdown data
  List _campuses    = [];
  List _classes     = [];
  List _sections    = [];
  List _subjects    = [];
  List _teachers    = [];
  List _schoolyears = [];

  // Selected values
  String? _selCampus;
  String? _selClass;
  String? _selSection;
  String? _selSubject;
  String? _selTeacher;
  String? _selSchoolyear;
  String? _selDay;

  // Time pickers
  TimeOfDay _startTime = const TimeOfDay(hour: 8,  minute: 0);
  TimeOfDay _endTime   = const TimeOfDay(hour: 9,  minute: 0);

  bool _isLoading = false;

  static const Color _primary = Color(0xFF1B5E20);
  static const Color _accent  = Color(0xFF66BB6A);

  static const List<String> _days = [
    'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
  ];

  // ── Init ────────────────────────────────────────────────────────────────
  @override
  void initState() {
    super.initState();
    _fetchMeta();

    if (widget.routineData != null) {
      final d = widget.routineData!;
      _roomCtrl.text    = (d['room'] ?? '').toString();
      _selCampus        = (d['campusID']     ?? '').toString();
      _selClass         = (d['classesID']    ?? '').toString();
      _selSection       = (d['sectionID']    ?? '').toString().isEmpty || d['sectionID'].toString() == '0' ? null : d['sectionID'].toString();
      _selSubject       = (d['subjectID']    ?? '').toString();
      _selTeacher       = (d['teacherID']    ?? '').toString().isEmpty || d['teacherID'].toString() == '0' ? null : d['teacherID'].toString();
      _selSchoolyear    = (d['schoolyearID'] ?? '').toString();
      _selDay           = _days.contains(d['day']) ? d['day'] : null;

      // Parse existing times
      final st = (d['start_time'] ?? '8:00').toString();
      final et = (d['end_time']   ?? '9:00').toString();
      _startTime = _parseTime(st);
      _endTime   = _parseTime(et);
    } else {
      _selCampus     = widget.preselectedCampusID;
      _selClass      = widget.preselectedClassesID;
      _selSection    = widget.preselectedSectionID;
      _selSchoolyear = widget.preselectedSchoolyearID;
    }
  }

  TimeOfDay _parseTime(String t) {
    try {
      // Handles "HH:MM" or "H:MM AM/PM"
      final parts = t.split(':');
      final hour = int.tryParse(parts[0].trim()) ?? 8;
      final minStr = parts.length > 1 ? parts[1].trim().replaceAll(RegExp(r'[^0-9]'), '') : '00';
      final min = int.tryParse(minStr) ?? 0;
      return TimeOfDay(hour: hour.clamp(0, 23), minute: min.clamp(0, 59));
    } catch (_) {
      return const TimeOfDay(hour: 8, minute: 0);
    }
  }

  String _formatTime(TimeOfDay t) {
    final h = t.hour.toString().padLeft(2, '0');
    final m = t.minute.toString().padLeft(2, '0');
    return '$h:$m';
  }

  @override
  void dispose() {
    _roomCtrl.dispose();
    super.dispose();
  }

  // ── API calls ────────────────────────────────────────────────────────────
  Future<void> _fetchMeta() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      // Fetch campuses
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

      // Fetch school years (from Syllabus API's shared endpoint)
      final yearRes = await http.post(
        Uri.parse('${base}api/schoolyears'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'adminID': widget.userData['adminID'] ?? 1}),
      );
      final yearResult = jsonDecode(yearRes.body);
      if (!mounted) return;
      if (yearResult['status'] == true) {
        setState(() {
          _schoolyears = yearResult['data'] ?? [];
          if ((_selSchoolyear == null || _selSchoolyear == '0') && _schoolyears.isNotEmpty) {
            _selSchoolyear = _schoolyears.first['schoolyearID'].toString();
          }
        });
      }

      // Prefill cascading dropdowns (edit mode)
      if (_selCampus != null) {
        await _fetchClasses(_selCampus!);
        await _fetchTeachers(_selCampus!);
        if (_selClass != null) {
          await _fetchSections(_selClass!);
          await _fetchSubjects(_selClass!);
        }
      }
    } catch (e) {
      dev.log('Routine form meta error: $e');
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
          _sections = [];
          _subjects = [];
          _teachers = [];
        }
      });
    } catch (e) {
      dev.log('Routine form fetch classes error: $e');
    }
  }

  Future<void> _fetchSections(String classesID) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/section'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'classesID': classesID, 'campusID': _selCampus ?? '0', 'adminID': widget.userData['adminID'] ?? 1}),
      );
      final result = jsonDecode(res.body);
      if (!mounted) return;
      setState(() {
        _sections = result['data'] ?? [];
        if (_selSection != null && _sections.every((s) => s['sectionID'].toString() != _selSection)) {
          _selSection = null;
        }
      });
    } catch (e) {
      dev.log('Routine form fetch sections error: $e');
    }
  }

  Future<void> _fetchSubjects(String classesID) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/subject'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'classesID': classesID, 'campusID': _selCampus ?? '0', 'adminID': widget.userData['adminID'] ?? 1}),
      );
      final result = jsonDecode(res.body);
      if (!mounted) return;
      setState(() {
        _subjects = result['data'] ?? [];
        if (_selSubject != null && _subjects.every((s) => s['subjectID'].toString() != _selSubject)) {
          _selSubject = null;
          _teachers   = [];
          _selTeacher = null;
        }
      });
    } catch (e) {
      dev.log('Routine form fetch subjects error: $e');
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
      if (!mounted) return;
      setState(() {
        _teachers = result['data'] ?? [];
        if (_selTeacher != null && _teachers.every((t) => t['teacherID'].toString() != _selTeacher)) {
          _selTeacher = null;
        }
      });
    } catch (e) {
      dev.log('Routine form fetch teachers error: $e');
    }
  }

  // ── Time pickers ─────────────────────────────────────────────────────────
  Future<void> _pickStartTime() async {
    final picked = await showTimePicker(context: context, initialTime: _startTime);
    if (picked != null) setState(() => _startTime = picked);
  }

  Future<void> _pickEndTime() async {
    final picked = await showTimePicker(context: context, initialTime: _endTime);
    if (picked != null) setState(() => _endTime = picked);
  }

  // ── Save ──────────────────────────────────────────────────────────────────
  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    // Extra validations
    if (_selCampus == null || _selCampus == '0') {
      _snack('Please select a campus'); return;
    }
    if (_selClass == null || _selClass == '0') {
      _snack('Please select a class'); return;
    }
    if (_selSection == null || _selSection == '0') {
      _snack('Please select a section'); return;
    }
    if (_selSubject == null || _selSubject == '0') {
      _snack('Please select a subject'); return;
    }
    if (_selTeacher == null || _selTeacher == '0') {
      _snack('Please select a teacher'); return;
    }
    if (_selSchoolyear == null || _selSchoolyear == '0') {
      _snack('Please select a school year'); return;
    }
    if (_selDay == null) {
      _snack('Please select a day'); return;
    }
    if (_roomCtrl.text.trim().isEmpty) {
      _snack('Please enter a room'); return;
    }

    final startStr = _formatTime(_startTime);
    final endStr   = _formatTime(_endTime);

    // Basic time sanity check
    if (_startTime.hour > _endTime.hour ||
        (_startTime.hour == _endTime.hour && _startTime.minute >= _endTime.minute)) {
      _snack('End time must be after start time');
      return;
    }

    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';

    final isEdit = widget.routineData != null;
    final apiUrl = isEdit ? '${base}api/routine_update' : '${base}api/routine_add';

    final body = <String, dynamic>{
      'campusID':     _selCampus,
      'classesID':    _selClass,
      'sectionID':    _selSection,
      'subjectID':    _selSubject,
      'teacherID':    _selTeacher,
      'schoolyearID': _selSchoolyear,
      'day':          _selDay,
      'start_time':   startStr,
      'end_time':     endStr,
      'room':         _roomCtrl.text.trim(),
      
      'adminID': widget.userData['adminID'] ?? 1,
      'create_userID': _getCreateUserID(),
      'create_username': widget.userData['username'] ?? widget.userData['name'] ?? 'admin',
      'create_usertype': widget.userData['user_type'] ?? 'Admin',

      if (isEdit) 'routineID': widget.routineData!['routineID'],
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
          SnackBar(content: Text(result['message'] ?? 'Saved')),
        );
      } else {
        _snack(result['message'] ?? 'Save failed');
      }
    } catch (e) {
      dev.log('Save routine error: $e');
      if (!mounted) return;
      _snack('Error: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _snack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  // ── Build ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final isEdit = widget.routineData != null;

    return Scaffold(
      appBar: AppBar(
        title: Text(isEdit ? 'Edit Routine' : 'Add Routine'),
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
                    // ── Campus ──────────────────────────────────────────────
                    _sectionLabel('Location & Class'),
                    DropdownButtonFormField<String>(
                      value: _selCampus,
                      decoration: _decor('Campus *', Icons.location_city),
                      items: _campuses.map((c) => DropdownMenuItem<String>(
                            value: c['campusID'].toString(),
                            child: Text(c['name'] ?? 'Campus'),
                          )).toList(),
                      onChanged: (v) {
                        setState(() {
                          _selCampus  = v;
                          _selClass   = null;
                          _selSection = null;
                          _selSubject = null;
                          _selTeacher = null;
                          _classes    = [];
                          _sections   = [];
                          _subjects   = [];
                          _teachers   = [];
                        });
                        if (v != null) {
                          _fetchClasses(v);
                          _fetchTeachers(v);
                        }
                      },
                      validator: (v) => v == null || v == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // ── Class ───────────────────────────────────────────────
                    DropdownButtonFormField<String>(
                      value: _selClass,
                      decoration: _decor('Class *', Icons.class_),
                      items: _classes.map((c) => DropdownMenuItem<String>(
                            value: c['classesID'].toString(),
                            child: Text(c['classes'] ?? 'Class'),
                          )).toList(),
                      onChanged: (v) {
                        setState(() {
                          _selClass   = v;
                          _selSection = null;
                          _selSubject = null;
                          _sections   = [];
                          _subjects   = [];
                        });
                        if (v != null) {
                          _fetchSections(v);
                          _fetchSubjects(v);
                        }
                      },
                      validator: (v) => v == null || v == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // ── Section ─────────────────────────────────────────────
                    DropdownButtonFormField<String>(
                      value: _selSection,
                      decoration: _decor('Section *', Icons.meeting_room),
                      items: _sections.map((s) => DropdownMenuItem<String>(
                            value: s['sectionID'].toString(),
                            child: Text(s['section'] ?? 'Section'),
                          )).toList(),
                      onChanged: (v) => setState(() => _selSection = v),
                      validator: (v) => v == null || v == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 20),

                    // ── Subject & Teacher ───────────────────────────────────
                    _sectionLabel('Subject & Teacher'),
                    DropdownButtonFormField<String>(
                      value: _selSubject,
                      decoration: _decor('Subject *', Icons.book),
                      items: _subjects.map((s) => DropdownMenuItem<String>(
                            value: s['subjectID'].toString(),
                            child: Text(s['subject'] ?? 'Subject'),
                          )).toList(),
                      onChanged: (v) => setState(() => _selSubject = v),
                      validator: (v) => v == null || v == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    DropdownButtonFormField<String>(
                      value: _selTeacher,
                      decoration: _decor('Teacher *', Icons.person),
                      items: _teachers.map((t) => DropdownMenuItem<String>(
                            value: t['teacherID'].toString(),
                            child: Text(t['name'] ?? 'Teacher'),
                          )).toList(),
                      onChanged: (v) => setState(() => _selTeacher = v),
                      validator: (v) => v == null || v == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 20),

                    // ── Schedule ────────────────────────────────────────────
                    _sectionLabel('Schedule'),
                    DropdownButtonFormField<String>(
                      value: _selSchoolyear,
                      decoration: _decor('School Year *', Icons.calendar_month),
                      items: _schoolyears.map((sy) => DropdownMenuItem<String>(
                            value: sy['schoolyearID'].toString(),
                            child: Text(sy['year']?.toString() ?? sy['schoolyear']?.toString() ?? sy['schoolyeartitle']?.toString() ?? 'Year'),
                          )).toList(),
                      onChanged: (v) => setState(() => _selSchoolyear = v),
                      validator: (v) => v == null || v == '0' ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    DropdownButtonFormField<String>(
                      value: _selDay,
                      decoration: _decor('Day *', Icons.today),
                      items: _days.map((d) => DropdownMenuItem<String>(
                            value: d,
                            child: Text(d),
                          )).toList(),
                      onChanged: (v) => setState(() => _selDay = v),
                      validator: (v) => v == null ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),

                    // Time row
                    Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: _pickStartTime,
                            child: InputDecorator(
                              decoration: _decor('Start Time *', Icons.access_time),
                              child: Text(_formatTime(_startTime), style: const TextStyle(fontSize: 16)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: InkWell(
                            onTap: _pickEndTime,
                            child: InputDecorator(
                              decoration: _decor('End Time *', Icons.access_time_filled),
                              child: Text(_formatTime(_endTime), style: const TextStyle(fontSize: 16)),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // ── Room ────────────────────────────────────────────────
                    _sectionLabel('Room'),
                    TextFormField(
                      controller: _roomCtrl,
                      decoration: _decor('Room No. / Name *', Icons.door_front_door),
                      validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
                    ),
                    const SizedBox(height: 28),

                    // ── Submit ──────────────────────────────────────────────
                    ElevatedButton.icon(
                      onPressed: _save,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      icon: Icon(isEdit ? Icons.save : Icons.add_circle_outline),
                      label: Text(isEdit ? 'Update Routine' : 'Add Routine', style: const TextStyle(fontSize: 16)),
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  // ── Helpers ───────────────────────────────────────────────────────────────
  Widget _sectionLabel(String label) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Text(label,
            style: TextStyle(
              fontWeight: FontWeight.bold,
              color: _primary,
              fontSize: 13,
              letterSpacing: 0.5,
            )),
      );

  InputDecoration _decor(String label, IconData icon) => InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, size: 20),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        isDense: true,
        contentPadding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
      );

  int _getCreateUserID() {
    final d = widget.userData;
    final keys = ['systemadminID', 'userID', 'teacherID', 'parentsID', 'studentID'];
    for (var k in keys) {
      if (d[k] != null) return int.tryParse(d[k].toString()) ?? 1;
    }
    return 1;
  }
}
