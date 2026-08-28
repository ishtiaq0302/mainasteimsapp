import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';
import 'routine_detail_page.dart';
import 'routine_form_page.dart';

class RoutineListPage extends StatefulWidget {
  final Map userData;

  const RoutineListPage({super.key, required this.userData});

  @override
  State<RoutineListPage> createState() => _RoutineListPageState();
}

class _RoutineListPageState extends State<RoutineListPage> {
  // ── Data lists ─────────────────────────────────────────────────────────
  List _routines  = [];
  List _filtered  = [];
  List _campuses  = [];
  List _classes   = [];
  List _sections  = [];
  List _schoolyears = [];

  // ── Selections ─────────────────────────────────────────────────────────
  String? _selCampus;
  String? _selClass;
  String? _selSection;
  String? _selSchoolyear;

  // ── UI state ───────────────────────────────────────────────────────────
  bool _isLoading = false;
  int  _rowsPerPage = 10;
  int  _currentPage = 0;

  final TextEditingController _searchCtrl = TextEditingController();

  static const Color _primary = Color(0xFF1B5E20); // deep green — distinct from campus/subject
  static const Color _accent  = Color(0xFF66BB6A);

  // Days order for sorting
  static const _dayOrder = {
    'Saturday': 0, 'Sunday': 1, 'Monday': 2, 'Tuesday': 3,
    'Wednesday': 4, 'Thursday': 5, 'Friday': 6,
  };

  @override
  void initState() {
    super.initState();
    _fetchMeta();
    _searchCtrl.addListener(_filter);
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  // ── Meta (campuses + schoolyears) ────────────────────────────────────
  Future<void> _fetchMeta() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      // Campuses via dedicated Campus API
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

      // Schoolyears via Syllabus API shared endpoint
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
    } catch (e) {
      dev.log('Routine meta error: $e');
    }
  }

  // ── Classes for selected campus ────────────────────────────────────────
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
        _classes    = result['data'] ?? [];
        _selClass   = null;
        _sections   = [];
        _selSection = null;
        _routines   = [];
        _filtered   = [];
      });
    } catch (e) {
      dev.log('Routine fetch classes error: $e');
    }
  }

  // ── Sections for selected class ────────────────────────────────────────
  Future<void> _fetchSections(String classesID) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/section'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'classesID': classesID,
          'campusID':  _selCampus ?? '0',
          'adminID':   widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(res.body);
      if (!mounted) return;
      setState(() {
        _sections   = result['data'] ?? [];
        _selSection = null;
        _routines   = [];
        _filtered   = [];
      });
    } catch (e) {
      dev.log('Routine fetch sections error: $e');
    }
  }

  // ── Routines ───────────────────────────────────────────────────────────
  Future<void> _fetchRoutines() async {
    if (_selCampus == null && _selClass == null) return;
    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final body = <String, dynamic>{
        'adminID': widget.userData['adminID'] ?? 1,
      };
      if (_selCampus != null)     body['campusID']     = _selCampus;
      if (_selClass != null)      body['classesID']    = _selClass;
      if (_selSection != null)    body['sectionID']    = _selSection;
      if (_selSchoolyear != null) body['schoolyearID'] = _selSchoolyear;

      final res = await http.post(
        Uri.parse('${base}api/routine'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );
      final result = jsonDecode(res.body);
      if (!mounted) return;
      setState(() {
        _routines    = result['data'] ?? [];
        _currentPage = 0;
        _filter();
        _isLoading   = false;
      });
    } catch (e) {
      dev.log('Fetch routines error: $e');
      if (mounted) setState(() => _isLoading = false);
    }
  }

  // ── Delete ─────────────────────────────────────────────────────────────
  Future<void> _delete(int id) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/routine_delete'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'routineID': id, 'adminID': widget.userData['adminID'] ?? 1}),
      );
      final result = jsonDecode(res.body);
      if (!mounted) return;
      if (result['status'] == true) {
        _fetchRoutines();
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Routine deleted')));
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result['message'] ?? 'Delete failed')));
      }
    } catch (e) {
      dev.log('Delete routine error: $e');
    }
  }

  void _confirmDelete(int id, String label) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Delete Routine'),
        content: Text('Delete "$label"? This cannot be undone.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          TextButton(
            onPressed: () { Navigator.pop(ctx); _delete(id); },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  // ── Filter ─────────────────────────────────────────────────────────────
  void _filter() {
    final q = _searchCtrl.text.trim().toLowerCase();
    setState(() {
      _filtered = q.isEmpty
          ? _routines
          : _routines.where((r) {
              final sub  = (r['subject_name']  ?? '').toString().toLowerCase();
              final sec  = (r['section_name']  ?? '').toString().toLowerCase();
              final tch  = (r['teacher_name']  ?? '').toString().toLowerCase();
              final day  = (r['day']           ?? '').toString().toLowerCase();
              final room = (r['room']          ?? '').toString().toLowerCase();
              return sub.contains(q) || sec.contains(q) || tch.contains(q) ||
                     day.contains(q) || room.contains(q);
            }).toList();
      // Sort by day order then start_time
      _filtered.sort((a, b) {
        final dayA = _dayOrder[a['day']] ?? 99;
        final dayB = _dayOrder[b['day']] ?? 99;
        if (dayA != dayB) return dayA.compareTo(dayB);
        return (a['start_time'] ?? '').toString().compareTo((b['start_time'] ?? '').toString());
      });
    });
  }

  // ── Day colour chip ────────────────────────────────────────────────────
  Color _dayColor(String? day) {
    switch (day) {
      case 'Saturday':  return Colors.purple.shade100;
      case 'Sunday':    return Colors.red.shade100;
      case 'Monday':    return Colors.blue.shade100;
      case 'Tuesday':   return Colors.teal.shade100;
      case 'Wednesday': return Colors.orange.shade100;
      case 'Thursday':  return Colors.green.shade100;
      case 'Friday':    return Colors.indigo.shade100;
      default:          return Colors.grey.shade100;
    }
  }

  // ── Build ──────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final totalPages = (_filtered.length / _rowsPerPage).ceil().clamp(1, 9999);
    final startIdx   = _currentPage * _rowsPerPage;
    final endIdx     = (startIdx + _rowsPerPage).clamp(0, _filtered.length);
    final pageItems  = _filtered.isEmpty ? [] : _filtered.sublist(startIdx, endIdx);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Class Routines'),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [_primary, _accent]),
          ),
        ),
        foregroundColor: Colors.white,
      ),

      body: Column(
        children: [
          // ── Filters panel ───────────────────────────────────────────────
          Container(
            padding: const EdgeInsets.all(12),
            color: Colors.grey.shade100,
            child: Column(
              children: [
                // Campus
                DropdownButtonFormField<String>(
                  value: _selCampus,
                  decoration: InputDecoration(
                    labelText: 'Campus',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    isDense: true,
                    prefixIcon: const Icon(Icons.location_city, size: 20),
                  ),
                  items: _campuses.map((c) => DropdownMenuItem<String>(
                        value: c['campusID'].toString(),
                        child: Text(c['name'] ?? 'Campus'),
                      )).toList(),
                  onChanged: (v) {
                    if (v == null) return;
                    setState(() {
                      _selCampus  = v;
                      _selClass   = null;
                      _selSection = null;
                      _classes    = [];
                      _sections   = [];
                      _routines   = [];
                      _filtered   = [];
                    });
                    _fetchClasses(v);
                    _fetchRoutines();
                  },
                ),

                if (_selCampus != null && _classes.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  // Class
                  DropdownButtonFormField<String>(
                    value: _selClass,
                    decoration: InputDecoration(
                      labelText: 'Class',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                      isDense: true,
                      prefixIcon: const Icon(Icons.class_, size: 20),
                    ),
                    items: _classes.map((c) => DropdownMenuItem<String>(
                          value: c['classesID'].toString(),
                          child: Text(c['classes'] ?? 'Class'),
                        )).toList(),
                    onChanged: (v) {
                      if (v == null) return;
                      setState(() { _selClass = v; _selSection = null; });
                      _fetchSections(v);
                      _fetchRoutines();
                    },
                  ),
                ],

                if (_selClass != null && _sections.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  // Section (optional)
                  DropdownButtonFormField<String>(
                    value: _selSection,
                    decoration: InputDecoration(
                      labelText: 'Section (optional)',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                      isDense: true,
                      prefixIcon: const Icon(Icons.meeting_room, size: 20),
                    ),
                    items: [
                      const DropdownMenuItem<String>(value: null, child: Text('All Sections')),
                      ..._sections.map((s) => DropdownMenuItem<String>(
                            value: s['sectionID'].toString(),
                            child: Text(s['section'] ?? 'Section'),
                          )),
                    ],
                    onChanged: (v) {
                      setState(() => _selSection = v);
                      _fetchRoutines();
                    },
                  ),
                ],

                if (_schoolyears.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  // School year (optional)
                  DropdownButtonFormField<String>(
                    value: _selSchoolyear,
                    decoration: InputDecoration(
                      labelText: 'School Year (optional)',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                      isDense: true,
                      prefixIcon: const Icon(Icons.calendar_month, size: 20),
                    ),
                    items: [
                      const DropdownMenuItem<String>(value: null, child: Text('All Years')),
                      ..._schoolyears.map((sy) => DropdownMenuItem<String>(
                            value: sy['schoolyearID'].toString(),
                            child: Text(sy['year'] ?? 'Year'),
                          )),
                    ],
                    onChanged: (v) {
                      setState(() => _selSchoolyear = v);
                      _fetchRoutines();
                    },
                  ),
                ],

                if (_selClass != null) ...[
                  const SizedBox(height: 8),
                  TextField(
                    controller: _searchCtrl,
                    decoration: InputDecoration(
                      hintText: 'Search subject, teacher, room…',
                      prefixIcon: const Icon(Icons.search),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                      isDense: true,
                      filled: true,
                      fillColor: Colors.white,
                    ),
                  ),
                ],
              ],
            ),
          ),

          // ── Stats chip ─────────────────────────────────────────────────
          if (_selClass != null)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
              child: Row(children: [
                Chip(
                  backgroundColor: _primary.withOpacity(0.1),
                  avatar: const Icon(Icons.schedule, color: _primary, size: 18),
                  label: Text(
                    '${_filtered.length} routine${_filtered.length == 1 ? '' : 's'}',
                    style: const TextStyle(color: _primary, fontWeight: FontWeight.w600),
                  ),
                ),
              ]),
            ),

          // ── Table ──────────────────────────────────────────────────────
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _selCampus == null
                    ? const Center(child: Text('Please select a campus'))
                    : _selClass == null
                        ? const Center(child: Text('Please select a class'))
                        : _filtered.isEmpty
                            ? Center(
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(Icons.schedule, size: 64, color: Colors.grey.shade400),
                                    const SizedBox(height: 12),
                                    Text('No routines found', style: TextStyle(color: Colors.grey.shade600)),
                                  ],
                                ),
                              )
                            : SingleChildScrollView(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.stretch,
                                  children: [
                                    LayoutBuilder(
                                      builder: (context, constraints) {
                                        return SingleChildScrollView(
                                          scrollDirection: Axis.horizontal,
                                          child: ConstrainedBox(
                                            constraints: BoxConstraints(minWidth: constraints.maxWidth),
                                            child: DataTable(
                                              headingRowColor: MaterialStateProperty.all(_primary.withOpacity(0.08)),
                                              columnSpacing: 16,
                                              columns: const [
                                                DataColumn(label: Text('#',        style: TextStyle(fontWeight: FontWeight.bold))),
                                                DataColumn(label: Text('Day',      style: TextStyle(fontWeight: FontWeight.bold))),
                                                DataColumn(label: Text('Time',     style: TextStyle(fontWeight: FontWeight.bold))),
                                                DataColumn(label: Text('Subject',  style: TextStyle(fontWeight: FontWeight.bold))),
                                                DataColumn(label: Text('Section',  style: TextStyle(fontWeight: FontWeight.bold))),
                                                DataColumn(label: Text('Teacher',  style: TextStyle(fontWeight: FontWeight.bold))),
                                                DataColumn(label: Text('Room',     style: TextStyle(fontWeight: FontWeight.bold))),
                                                DataColumn(label: Text('Actions',  style: TextStyle(fontWeight: FontWeight.bold))),
                                              ],
                                              rows: List.generate(pageItems.length, (i) {
                                                final item      = pageItems[i];
                                                final routineID = int.tryParse((item['routineID'] ?? '0').toString()) ?? 0;
                                                final day       = item['day']?.toString() ?? '';
                                                final label     = '${item['subject_name'] ?? ''} - $day';

                                                return DataRow(cells: [
                                                  DataCell(Text('${startIdx + i + 1}')),
                                                  DataCell(
                                                    Container(
                                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                      decoration: BoxDecoration(
                                                        color: _dayColor(day),
                                                        borderRadius: BorderRadius.circular(12),
                                                      ),
                                                      child: Text(day, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                                                    ),
                                                  ),
                                                  DataCell(Text('${item['start_time'] ?? ''}–${item['end_time'] ?? ''}', style: const TextStyle(fontSize: 12))),
                                                  DataCell(Text(item['subject_name'] ?? '—', style: const TextStyle(fontWeight: FontWeight.w500))),
                                                  DataCell(Text(item['section_name'] ?? '—')),
                                                  DataCell(Text(item['teacher_name'] ?? '—')),
                                                  DataCell(Text(item['room'] ?? '—')),
                                                  DataCell(
                                                    PopupMenuButton<String>(
                                                      icon: const Icon(Icons.more_vert),
                                                      onSelected: (val) {
                                                        if (val == 'detail') {
                                                          Navigator.push(context, MaterialPageRoute(
                                                            builder: (_) => RoutineDetailPage(routineID: routineID, userData: widget.userData),
                                                          ));
                                                        } else if (val == 'edit') {
                                                          Navigator.push(context, MaterialPageRoute(
                                                            builder: (_) => RoutineFormPage(routineData: item, userData: widget.userData),
                                                          )).then((ok) { if (ok == true) _fetchRoutines(); });
                                                        } else if (val == 'delete') {
                                                          _confirmDelete(routineID, label);
                                                        }
                                                      },
                                                      itemBuilder: (_) => const [
                                                        PopupMenuItem(value: 'detail', child: Text('Detail')),
                                                        PopupMenuItem(value: 'edit',   child: Text('Edit')),
                                                        PopupMenuItem(value: 'delete', child: Text('Delete')),
                                                      ],
                                                    ),
                                                  ),
                                                ]);
                                              }),
                                            ),
                                          ),
                                        );
                                      },
                                    ),

                                    // Pagination
                                    Padding(
                                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                                      child: Row(
                                        children: [
                                          DropdownButton<int>(
                                            value: _rowsPerPage,
                                            underline: const SizedBox(),
                                            items: [5, 10, 20, 50].map((v) => DropdownMenuItem<int>(
                                                  value: v,
                                                  child: Text('$v rows'),
                                                )).toList(),
                                            onChanged: (v) {
                                              if (v == null) return;
                                              setState(() { _rowsPerPage = v; _currentPage = 0; });
                                            },
                                          ),
                                          const SizedBox(width: 12),
                                          Text(
                                            '${_filtered.isEmpty ? 0 : startIdx + 1}–$endIdx of ${_filtered.length}',
                                            style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                                          ),
                                          const Spacer(),
                                          IconButton(
                                            icon: const Icon(Icons.chevron_left),
                                            onPressed: _currentPage > 0 ? () => setState(() => _currentPage--) : null,
                                          ),
                                          IconButton(
                                            icon: const Icon(Icons.chevron_right),
                                            onPressed: (_currentPage + 1) < totalPages ? () => setState(() => _currentPage++) : null,
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
          ),
        ],
      ),

      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          Navigator.push(context, MaterialPageRoute(
            builder: (_) => RoutineFormPage(
              userData: widget.userData,
              preselectedCampusID:  _selCampus,
              preselectedClassesID: _selClass,
              preselectedSectionID: _selSection,
              preselectedSchoolyearID: _selSchoolyear,
            ),
          )).then((ok) { if (ok == true && _selClass != null) _fetchRoutines(); });
        },
        backgroundColor: _primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Add Routine', style: TextStyle(color: Colors.white)),
      ),
    );
  }
}
