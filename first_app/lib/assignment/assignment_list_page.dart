import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';

import 'package:first_app/config.dart';
import 'assignment_form_page.dart';

class AssignmentListPage extends StatefulWidget {
  final Map userData;

  const AssignmentListPage({super.key, required this.userData});

  @override
  State<AssignmentListPage> createState() => _AssignmentListPageState();
}

class _AssignmentListPageState extends State<AssignmentListPage> {
  List _assignments = [];
  List _filteredAssignments = [];
  List _campuses = [];
  List _classes  = [];

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
    _searchController.addListener(_filterAssignments);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

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
          _classes              = result['data'] ?? [];
          _selectedClass        = null;
          _assignments          = [];
          _filteredAssignments  = [];
        });
      }
    } catch (e) {
      dev.log('Fetch classes error: $e');
    }
  }

  Future<void> _fetchAssignments(String classesID) async {
    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/assignment'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID':     _selectedCampus ?? '0',
          'classesID':    classesID,
          'schoolyearID': widget.userData['defaultschoolyearID'] ?? 0,
          'adminID':      widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      setState(() {
        _assignments = result['data'] ?? [];
        _currentPage = 0;
        _filterAssignments();
        _isLoading = false;
      });
    } catch (e) {
      dev.log('Fetch assignments error: $e');
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  void _filterAssignments() {
    final query = _searchController.text.trim().toLowerCase();
    setState(() {
      if (query.isEmpty) {
        _filteredAssignments = _assignments;
      } else {
        _filteredAssignments = _assignments.where((item) {
          final title   = (item['title']        ?? '').toString().toLowerCase();
          final subject = (item['subject_name'] ?? '').toString().toLowerCase();
          return title.contains(query) || subject.contains(query);
        }).toList();
      }
    });
  }

  Future<void> _downloadFile(String? fileUrl) async {
    if (fileUrl == null || fileUrl.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No attachment file available for download')),
      );
      return;
    }
    final uri = Uri.parse(fileUrl);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        await launchUrl(uri);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not download file: $e')),
        );
      }
    }
  }

  Future<void> _deleteAssignment(int id) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/assignment_delete'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'assignmentID': id,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      if (result['status'] == true && _selectedClass != null) {
        _fetchAssignments(_selectedClass!);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Assignment deleted')),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Delete failed')),
        );
      }
    } catch (e) {
      dev.log('Delete assignment error: $e');
    }
  }

  void _confirmDelete(int id, String title) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Assignment'),
        content: Text('Are you sure you want to delete "$title"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              _deleteAssignment(id);
            },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final totalPages = (_filteredAssignments.length / _rowsPerPage).ceil();
    final startIdx   = _currentPage * _rowsPerPage;
    final endIdx     = (startIdx + _rowsPerPage).clamp(0, _filteredAssignments.length);
    final pageItems  = _filteredAssignments.sublist(startIdx, endIdx);

    return Scaffold(
      appBar: AppBar(title: const Text('Assignments')),
      body: Column(
        children: [
          // ── Filters ───────────────────────────────────────────────
          Container(
            padding: const EdgeInsets.all(10),
            color: Colors.grey.shade100,
            child: Column(
              children: [
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
                      _selectedCampus      = value;
                      _selectedClass       = null;
                      _assignments         = [];
                      _filteredAssignments = [];
                    });
                    _fetchClasses(value);
                  },
                ),

                if (_selectedCampus != null && _classes.isNotEmpty) ...[
                  const SizedBox(height: 8),
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
                      _fetchAssignments(value);
                    },
                  ),
                ],

                if (_selectedClass != null) ...[
                  const SizedBox(height: 8),
                  TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      labelText: 'Search assignment…',
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
                        ? const Center(child: Text('Please select a class to view assignments'))
                        : _filteredAssignments.isEmpty
                            ? const Center(child: Text('No assignments found'))
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
                                                DataColumn(label: Text('Title')),
                                                DataColumn(label: Text('Subject')),
                                                DataColumn(label: Text('Deadline')),
                                                DataColumn(label: Text('File')),
                                                DataColumn(label: Text('Action')),
                                              ],
                                              rows: List.generate(pageItems.length, (index) {
                                                final item         = pageItems[index];
                                                final assignmentID = int.tryParse(
                                                  (item['assignmentID'] ?? '0').toString(),
                                                ) ?? 0;
                                                final title   = item['title'] ?? '';
                                                final fileUrl = item['file_url'] ?? '';
                                                final hasFile = fileUrl.toString().isNotEmpty;

                                                return DataRow(cells: [
                                                  DataCell(Text((startIdx + index + 1).toString())),
                                                  DataCell(
                                                    SizedBox(
                                                      width: 140,
                                                      child: Text(
                                                        title,
                                                        overflow: TextOverflow.ellipsis,
                                                      ),
                                                    ),
                                                  ),
                                                  DataCell(Text(item['subject_name'] ?? '')),
                                                  DataCell(Text((item['deadlinedate'] ?? '').toString())),
                                                  DataCell(
                                                    hasFile
                                                        ? IconButton(
                                                            icon: const Icon(Icons.download, color: Colors.blue),
                                                            tooltip: 'Download File',
                                                            onPressed: () => _downloadFile(fileUrl),
                                                          )
                                                        : const Text('-', style: TextStyle(color: Colors.grey)),
                                                  ),
                                                  DataCell(
                                                    PopupMenuButton<String>(
                                                      icon: const Icon(Icons.more_vert),
                                                      onSelected: (value) {
                                                        if (value == 'download') {
                                                          _downloadFile(fileUrl);
                                                        } else if (value == 'edit') {
                                                          Navigator.push(
                                                            context,
                                                            MaterialPageRoute(
                                                              builder: (_) => AssignmentFormPage(
                                                                assignmentData: item,
                                                                userData: widget.userData,
                                                              ),
                                                            ),
                                                          ).then((ok) {
                                                            if (ok == true && _selectedClass != null) {
                                                              _fetchAssignments(_selectedClass!);
                                                            }
                                                          });
                                                        } else if (value == 'delete') {
                                                          _confirmDelete(assignmentID, title);
                                                        }
                                                      },
                                                      itemBuilder: (_) => [
                                                        if (hasFile)
                                                          const PopupMenuItem(
                                                            value: 'download',
                                                            child: Row(
                                                              children: [
                                                                Icon(Icons.download, size: 18, color: Colors.blue),
                                                                SizedBox(width: 8),
                                                                Text('Download'),
                                                              ],
                                                            ),
                                                          ),
                                                        const PopupMenuItem(value: 'edit', child: Text('Edit')),
                                                        const PopupMenuItem(value: 'delete', child: Text('Delete')),
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
                                            '${startIdx + 1}–$endIdx of ${_filteredAssignments.length}',
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

      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => AssignmentFormPage(
                userData: widget.userData,
                preselectedCampusID: _selectedCampus,
                preselectedClassesID: _selectedClass,
              ),
            ),
          ).then((ok) {
            if (ok == true && _selectedClass != null) {
              _fetchAssignments(_selectedClass!);
            }
          });
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
