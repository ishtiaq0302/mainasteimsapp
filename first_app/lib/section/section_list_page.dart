import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

import 'section_detail_page.dart';
import 'section_form_page.dart';

class SectionListPage extends StatefulWidget {
  final Map userData;

  const SectionListPage({super.key, required this.userData});

  @override
  State<SectionListPage> createState() => _SectionListPageState();
}

class _SectionListPageState extends State<SectionListPage> {
  List _sections = [];
  List _filteredSections = [];
  List _campuses = [];
  List _classes = [];

  String? _selectedCampus;
  String? _selectedClass;

  bool _isLoading = false;
  int _rowsPerPage = 10;
  int _currentPage = 0;

  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchCampuses();
    _searchController.addListener(_filterSections);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  // ── Campuses ──────────────────────────────────────────────────────
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

  // ── Classes for selected campus ───────────────────────────────────
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
          _selectedClass = null;
          _sections = [];
          _filteredSections = [];
        });
      }
    } catch (e) {
      dev.log('Fetch classes error: $e');
    }
  }

  // ── Sections for selected class ───────────────────────────────────
  Future<void> _fetchSections(String classesID) async {
    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/section'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID': _selectedCampus ?? '0',
          'classesID': classesID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      setState(() {
        _sections = result['data'] ?? [];
        _currentPage = 0;
        _filterSections();
        _isLoading = false;
      });
    } catch (e) {
      dev.log('Fetch sections error: $e');
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  // ── Search filter ─────────────────────────────────────────────────
  void _filterSections() {
    final query = _searchController.text.trim().toLowerCase();
    setState(() {
      if (query.isEmpty) {
        _filteredSections = _sections;
      } else {
        _filteredSections = _sections.where((item) {
          final name     = (item['section']      ?? '').toString().toLowerCase();
          final category = (item['category']     ?? '').toString().toLowerCase();
          final teacher  = (item['teacher_name'] ?? '').toString().toLowerCase();
          return name.contains(query) ||
              category.contains(query) ||
              teacher.contains(query);
        }).toList();
      }
    });
  }

  // ── Delete ────────────────────────────────────────────────────────
  Future<void> _deleteSection(int id) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/section_delete'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'sectionID': id,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      if (result['status'] == true && _selectedClass != null) {
        _fetchSections(_selectedClass!);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Section deleted')),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Delete failed')),
        );
      }
    } catch (e) {
      dev.log('Delete section error: $e');
    }
  }

  // ── Delete confirm dialog ─────────────────────────────────────────
  void _confirmDelete(int id, String name) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Section'),
        content: Text('Are you sure you want to delete "$name"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              _deleteSection(id);
            },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  // ── Build ─────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final totalPages = (_filteredSections.length / _rowsPerPage).ceil();
    final startIdx   = _currentPage * _rowsPerPage;
    final endIdx     = (startIdx + _rowsPerPage).clamp(0, _filteredSections.length);
    final pageItems  = _filteredSections.sublist(startIdx, endIdx);

    return Scaffold(
      appBar: AppBar(title: const Text('Sections')),
      body: Column(
        children: [
          // ── Filters ───────────────────────────────────────────────
          Container(
            padding: const EdgeInsets.all(10),
            color: Colors.grey.shade100,
            child: Column(
              children: [
                // Campus dropdown
                DropdownButtonFormField<String>(
                  value: _selectedCampus,
                  decoration: const InputDecoration(
                    labelText: 'Select Campus',
                    border: OutlineInputBorder(),
                    isDense: true,
                  ),
                  items: _campuses
                      .map((c) => DropdownMenuItem<String>(
                            value: c['campusID'].toString(),
                            child: Text(c['name'] ?? 'Campus'),
                          ))
                      .toList(),
                  onChanged: (value) {
                    if (value == null) return;
                    setState(() {
                      _selectedCampus = value;
                      _selectedClass  = null;
                      _sections       = [];
                      _filteredSections = [];
                    });
                    _fetchClasses(value);
                  },
                ),

                if (_selectedCampus != null && _classes.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  // Class dropdown
                  DropdownButtonFormField<String>(
                    value: _selectedClass,
                    decoration: const InputDecoration(
                      labelText: 'Select Class',
                      border: OutlineInputBorder(),
                      isDense: true,
                    ),
                    items: _classes
                        .map((c) => DropdownMenuItem<String>(
                              value: c['classesID'].toString(),
                              child: Text(c['classes'] ?? 'Class'),
                            ))
                        .toList(),
                    onChanged: (value) {
                      if (value == null) return;
                      setState(() => _selectedClass = value);
                      _fetchSections(value);
                    },
                  ),
                ],

                if (_selectedClass != null) ...[
                  const SizedBox(height: 8),
                  TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      labelText: 'Search section…',
                      prefixIcon: Icon(Icons.search),
                      border: OutlineInputBorder(),
                      isDense: true,
                    ),
                  ),
                ],
              ],
            ),
          ),

          // ── Table ─────────────────────────────────────────────────
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _selectedCampus == null
                    ? const Center(child: Text('Please select a campus'))
                    : _selectedClass == null
                        ? const Center(child: Text('Please select a class to view sections'))
                        : _filteredSections.isEmpty
                            ? const Center(child: Text('No sections found'))
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
                                              columnSpacing: 20,
                                              columns: const [
                                                DataColumn(label: Text('#')),
                                                DataColumn(label: Text('Section')),
                                                DataColumn(label: Text('Category')),
                                                DataColumn(label: Text('Capacity')),
                                                DataColumn(label: Text('Teacher')),
                                                DataColumn(label: Text('Note')),
                                                DataColumn(label: Text('Action')),
                                              ],
                                              rows: List.generate(pageItems.length, (index) {
                                                final item     = pageItems[index];
                                                final sectionID = int.tryParse(
                                                  (item['sectionID'] ?? '0').toString(),
                                                ) ?? 0;
                                                final sectionName = item['section'] ?? '';

                                                return DataRow(cells: [
                                                  DataCell(Text((startIdx + index + 1).toString())),
                                                  DataCell(Text(sectionName)),
                                                  DataCell(Text(item['category']     ?? '')),
                                                  DataCell(Text((item['capacity']    ?? '').toString())),
                                                  DataCell(Text(item['teacher_name'] ?? item['teacherID']?.toString() ?? 'N/A')),
                                                  DataCell(Text((item['note']        ?? '').toString())),
                                                  DataCell(
                                                    PopupMenuButton<String>(
                                                      icon: const Icon(Icons.more_vert),
                                                      onSelected: (value) {
                                                        if (value == 'detail') {
                                                          Navigator.push(
                                                            context,
                                                            MaterialPageRoute(
                                                              builder: (_) => SectionDetailPage(
                                                                sectionID: sectionID,
                                                                userData: widget.userData,
                                                              ),
                                                            ),
                                                          );
                                                        } else if (value == 'edit') {
                                                          Navigator.push(
                                                            context,
                                                            MaterialPageRoute(
                                                              builder: (_) => SectionFormPage(
                                                                sectionData: item,
                                                                userData: widget.userData,
                                                              ),
                                                            ),
                                                          ).then((ok) {
                                                            if (ok == true && _selectedClass != null) {
                                                              _fetchSections(_selectedClass!);
                                                            }
                                                          });
                                                        } else if (value == 'delete') {
                                                          _confirmDelete(sectionID, sectionName);
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

                                    // ── Pagination ─────────────────────────────────────
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
                                            '${startIdx + 1}–$endIdx of ${_filteredSections.length}',
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

      // ── FAB – Add section ────────────────────────────────────────
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          if (_selectedCampus == null || _selectedClass == null) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Please select a campus and class first')),
            );
            return;
          }
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => SectionFormPage(
                userData: widget.userData,
                preselectedCampusID: _selectedCampus,
                preselectedClassesID: _selectedClass,
              ),
            ),
          ).then((ok) {
            if (ok == true && _selectedClass != null) {
              _fetchSections(_selectedClass!);
            }
          });
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
