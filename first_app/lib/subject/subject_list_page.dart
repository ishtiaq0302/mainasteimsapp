import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';
import 'subject_form_page.dart';

class SubjectListPage extends StatefulWidget {
  final Map userData;

  const SubjectListPage({super.key, required this.userData});

  @override
  State<SubjectListPage> createState() => _SubjectListPageState();
}

class _SubjectListPageState extends State<SubjectListPage> {
  List _subjects = [];
  List _filteredSubjects = [];
  List _campuses = [];
  List _classes  = [];

  String? _selectedCampus = '0';
  String? _selectedClass  = '0';

  bool _isLoading = false;
  int _rowsPerPage = 10;
  int _currentPage = 0;

  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchCampuses();
    _fetchClasses('0');
    _fetchSubjects(campusID: '0', classesID: '0');
    _searchController.addListener(_filterSubjects);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  // ── Fetch Campuses ────────────────────────────────────────────────
  Future<void> _fetchCampuses() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/metadata'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID': 0,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() => _campuses = result['data']['campuses'] ?? []);
      }
    } catch (e) {
      dev.log('Fetch campuses error: $e');
    }
  }

  // ── Fetch Classes ─────────────────────────────────────────────────
  Future<void> _fetchClasses(String campusID) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/classes'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID': campusID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _classes = result['data'] ?? [];
        });
      }
    } catch (e) {
      dev.log('Fetch classes error: $e');
    }
  }

  // ── Fetch Subjects ────────────────────────────────────────────────
  Future<void> _fetchSubjects({String? campusID, String? classesID}) async {
    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';

    final cID  = campusID  ?? _selectedCampus ?? '0';
    final clID = classesID ?? _selectedClass  ?? '0';

    try {
      final response = await http.post(
        Uri.parse('${base}api/subject'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID':  cID,
          'classesID': clID,
          'adminID':   widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      setState(() {
        _subjects = result['data'] ?? [];
        _currentPage = 0;
        _filterSubjects();
        _isLoading = false;
      });
    } catch (e) {
      dev.log('Fetch subjects error: $e');
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  // ── Search filter ─────────────────────────────────────────────────
  void _filterSubjects() {
    final query = _searchController.text.trim().toLowerCase();
    setState(() {
      if (query.isEmpty) {
        _filteredSubjects = _subjects;
      } else {
        _filteredSubjects = _subjects.where((item) {
          final name    = (item['subject']        ?? '').toString().toLowerCase();
          final author  = (item['subject_author'] ?? '').toString().toLowerCase();
          final code    = (item['subject_code']   ?? '').toString().toLowerCase();
          final teacher = (item['teacher_name']   ?? '').toString().toLowerCase();
          final type    = (item['type']           ?? '').toString().toLowerCase();
          return name.contains(query) ||
              author.contains(query) ||
              code.contains(query) ||
              teacher.contains(query) ||
              type.contains(query);
        }).toList();
      }
    });
  }

  // ── Delete Subject ────────────────────────────────────────────────
  Future<void> _deleteSubject(int id) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/subject_delete'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'subjectID': id,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      if (result['status'] == true) {
        _fetchSubjects(campusID: _selectedCampus, classesID: _selectedClass);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Subject deleted')),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Delete failed')),
        );
      }
    } catch (e) {
      dev.log('Delete subject error: $e');
    }
  }

  void _confirmDelete(int id, String name) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Subject'),
        content: Text('Are you sure you want to delete "$name"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              _deleteSubject(id);
            },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  // ── Format Type Label ─────────────────────────────────────────────
  String _formatType(dynamic rawType) {
    if (rawType == null) return 'Compulsory';
    final str = rawType.toString().trim();
    if (str == '1' || str.toLowerCase() == 'compulsory') return 'Compulsory';
    if (str == '0' || str.toLowerCase() == 'optional') return 'Optional';
    return str.isNotEmpty ? str : 'Compulsory';
  }

  // ── Build ─────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final totalPages = (_filteredSubjects.length / _rowsPerPage).ceil();
    final startIdx   = _currentPage * _rowsPerPage;
    final endIdx     = (startIdx + _rowsPerPage).clamp(0, _filteredSubjects.length);
    final pageItems  = _filteredSubjects.sublist(startIdx, endIdx);

    return Scaffold(
      appBar: AppBar(title: const Text('Subjects')),
      body: Column(
        children: [
          // ── Filter Controls ─────────────────────────────────────────
          Container(
            padding: const EdgeInsets.all(10),
            color: Colors.grey.shade100,
            child: Column(
              children: [
                // Campus Dropdown
                DropdownButtonFormField<String>(
                  value: _selectedCampus,
                  decoration: const InputDecoration(
                    labelText: 'Select Campus',
                    border: OutlineInputBorder(),
                    isDense: true,
                  ),
                  items: [
                    const DropdownMenuItem<String>(
                      value: '0',
                      child: Text('Select Campus'),
                    ),
                    ..._campuses.map((c) => DropdownMenuItem<String>(
                          value: c['campusID'].toString(),
                          child: Text(c['name'] ?? 'Campus'),
                        )),
                  ],
                  onChanged: (value) {
                    if (value == null) return;
                    setState(() {
                      _selectedCampus = value;
                      _selectedClass  = '0';
                    });
                    _fetchClasses(value);
                    _fetchSubjects(campusID: value, classesID: '0');
                  },
                ),

                const SizedBox(height: 8),

                // Class Dropdown
                DropdownButtonFormField<String>(
                  value: _selectedClass,
                  decoration: const InputDecoration(
                    labelText: 'Select Class',
                    border: OutlineInputBorder(),
                    isDense: true,
                  ),
                  items: [
                    const DropdownMenuItem<String>(
                      value: '0',
                      child: Text('Select Class'),
                    ),
                    ..._classes.map((c) => DropdownMenuItem<String>(
                          value: c['classesID'].toString(),
                          child: Text(c['classes'] ?? 'Class'),
                        )),
                  ],
                  onChanged: (value) {
                    if (value == null) return;
                    setState(() => _selectedClass = value);
                    _fetchSubjects(campusID: _selectedCampus, classesID: value);
                  },
                ),

                const SizedBox(height: 8),

                // Search field
                TextField(
                  controller: _searchController,
                  decoration: const InputDecoration(
                    labelText: 'Search subject…',
                    prefixIcon: Icon(Icons.search),
                    border: OutlineInputBorder(),
                    isDense: true,
                  ),
                ),
              ],
            ),
          ),

          // ── Data Table ────────────────────────────────────────────
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _filteredSubjects.isEmpty
                    ? const Center(child: Text('No subjects found'))
                    : SingleChildScrollView(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            LayoutBuilder(
                              builder: (context, constraints) {
                                return SingleChildScrollView(
                                  scrollDirection: Axis.horizontal,
                                  child: ConstrainedBox(
                                    constraints: BoxConstraints(
                                      minWidth: constraints.maxWidth,
                                    ),
                                    child: DataTable(
                                      columnSpacing: 16,
                                      columns: const [
                                        DataColumn(label: Text('#')),
                                        DataColumn(label: Text('Name')),
                                        DataColumn(label: Text('Author')),
                                        DataColumn(label: Text('Code')),
                                        DataColumn(label: Text('Teacher')),
                                        DataColumn(label: Text('Pass Mark')),
                                        DataColumn(label: Text('Final Mark')),
                                        DataColumn(label: Text('Type')),
                                        DataColumn(label: Text('Action')),
                                      ],
                                      rows: List.generate(pageItems.length, (index) {
                                        final item      = pageItems[index];
                                        final subjectID = int.tryParse(
                                          (item['subjectID'] ?? '0').toString(),
                                        ) ?? 0;
                                        final subjectName  = (item['subject'] ?? '').toString();
                                        final author       = (item['subject_author'] ?? '').toString();
                                        final code         = (item['subject_code'] ?? '').toString();
                                        final teacher      = (item['teacher_name'] ?? item['teacherID']?.toString() ?? 'N/A').toString();
                                        final passMark     = (item['passmark'] ?? '').toString();
                                        final finalMark    = (item['finalmark'] ?? '').toString();
                                        final typeLabel    = _formatType(item['type']);

                                        return DataRow(cells: [
                                          DataCell(Text((startIdx + index + 1).toString())),
                                          DataCell(Text(subjectName)),
                                          DataCell(Text(author.isNotEmpty ? author : '-')),
                                          DataCell(Text(code)),
                                          DataCell(Text(teacher.isNotEmpty ? teacher : 'N/A')),
                                          DataCell(Text(passMark)),
                                          DataCell(Text(finalMark)),
                                          DataCell(Text(typeLabel)),
                                          DataCell(
                                            PopupMenuButton<String>(
                                              icon: const Icon(Icons.more_vert),
                                              onSelected: (value) {
                                                if (value == 'edit') {
                                                  Navigator.push(
                                                    context,
                                                    MaterialPageRoute(
                                                      builder: (_) => SubjectFormPage(
                                                        subjectData: item,
                                                        userData: widget.userData,
                                                      ),
                                                    ),
                                                  ).then((ok) {
                                                    if (ok == true) {
                                                      _fetchSubjects(
                                                        campusID: _selectedCampus,
                                                        classesID: _selectedClass,
                                                      );
                                                    }
                                                  });
                                                } else if (value == 'delete') {
                                                  _confirmDelete(subjectID, subjectName);
                                                }
                                              },
                                              itemBuilder: (_) => const [
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

                            // ── Pagination ───────────────────────────────────
                            Padding(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 16,
                                vertical: 8,
                              ),
                              child: Row(
                                children: [
                                  DropdownButton<int>(
                                    value: _rowsPerPage,
                                    underline: const SizedBox(),
                                    items: [5, 10, 20, 50].map((v) {
                                      return DropdownMenuItem<int>(
                                        value: v,
                                        child: Text('$v rows'),
                                      );
                                    }).toList(),
                                    onChanged: (v) {
                                      if (v == null) return;
                                      setState(() {
                                        _rowsPerPage = v;
                                        _currentPage = 0;
                                      });
                                    },
                                  ),
                                  const SizedBox(width: 12),
                                  Text(
                                    '${startIdx + 1}–$endIdx of ${_filteredSubjects.length}',
                                    style: TextStyle(
                                      color: Colors.grey.shade700,
                                      fontSize: 13,
                                    ),
                                  ),
                                  const Spacer(),
                                  IconButton(
                                    icon: const Icon(Icons.chevron_left),
                                    onPressed: _currentPage > 0
                                        ? () => setState(() => _currentPage--)
                                        : null,
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.chevron_right),
                                    onPressed: (_currentPage + 1) < totalPages
                                        ? () => setState(() => _currentPage++)
                                        : null,
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

      // ── FAB – Add Subject ──────────────────────────────────────────
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => SubjectFormPage(
                userData: widget.userData,
                preselectedCampusID: _selectedCampus,
                preselectedClassesID: _selectedClass,
              ),
            ),
          ).then((ok) {
            if (ok == true) {
              _fetchSubjects(
                campusID: _selectedCampus,
                classesID: _selectedClass,
              );
            }
          });
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
