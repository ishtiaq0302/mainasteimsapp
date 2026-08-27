import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

import 'class_detail_page.dart';
import 'class_form_page.dart';

class ClassListPage extends StatefulWidget {
  final Map userData;

  const ClassListPage({super.key, required this.userData});

  @override
  State<ClassListPage> createState() => _ClassListPageState();
}

class _ClassListPageState extends State<ClassListPage> {
  List _classes = [];
  List _filteredClasses = [];
  List _campuses = [];
  String? _selectedCampus;
  bool _isLoading = false;
  int _rowsPerPage = 10;
  int _currentPage = 0;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchCampuses();
    _searchController.addListener(_filterClasses);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchCampuses() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/metadata';

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID': 0,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
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

  Future<void> _fetchClasses(String campusID) async {
    setState(() => _isLoading = true);

    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/classes';

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
      if (!mounted) return;

      setState(() {
        _classes = result['data'] ?? [];
        _currentPage = 0;
        _filterClasses();
        _isLoading = false;
      });
    } catch (e) {
      dev.log('Fetch classes error: $e');
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  void _filterClasses() {
    final query = _searchController.text.trim().toLowerCase();

    setState(() {
      if (query.isEmpty) {
        _filteredClasses = _classes;
      } else {
        _filteredClasses = _classes.where((item) {
          final name = (item['classes'] ?? '').toString().toLowerCase();
          final numeric = (item['classes_numeric'] ?? '')
              .toString()
              .toLowerCase();
          final teacher = (item['teacher_name'] ?? item['teacherID'] ?? '')
              .toString()
              .toLowerCase();
          return name.contains(query) ||
              numeric.contains(query) ||
              teacher.contains(query);
        }).toList();
      }
    });
  }

  Future<void> _deleteClass(int id) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/classes_delete';

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'classesID': id,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );

      final result = jsonDecode(response.body);
      if (result['status'] == true && _selectedCampus != null) {
        _fetchClasses(_selectedCampus!);
        if (!mounted) return;
        ScaffoldMessenger.of(context)
            .showSnackBar(const SnackBar(content: Text('Class deleted')));
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Delete failed')),
        );
      }
    } catch (e) {
      dev.log('Delete class error: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('${AppConfig.appSettings['sname']} Classes')),
      body: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            color: Colors.grey.shade100,
            child: Column(
              children: [
                DropdownButtonFormField<String>(
                  initialValue: _selectedCampus,
                  decoration: const InputDecoration(
                    labelText: 'Select Campus',
                    border: OutlineInputBorder(),
                  ),
                  items: _campuses
                      .map(
                        (campus) => DropdownMenuItem<String>(
                          value: campus['campusID'].toString(),
                          child: Text(campus['name'] ?? 'Campus'),
                        ),
                      )
                      .toList(),
                  onChanged: (value) {
                    if (value == null) return;
                    setState(() => _selectedCampus = value);
                    _fetchClasses(value);
                  },
                ),
                if (_selectedCampus != null) ...[
                  const SizedBox(height: 10),
                  TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      labelText: 'Search class...',
                      prefixIcon: Icon(Icons.search),
                      border: OutlineInputBorder(),
                    ),
                  ),
                ],
              ],
            ),
          ),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _selectedCampus == null
                ? const Center(
                    child: Text('Please select a campus to view classes'),
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
                                constraints: BoxConstraints(
                                  minWidth: constraints.maxWidth,
                                ),
                                child: DataTable(
                                  columnSpacing: 20,
                                  columns: const [
                                    DataColumn(label: Text('#')),
                                    DataColumn(label: Text('Class')),
                                    DataColumn(label: Text('Numeric')),
                                    DataColumn(label: Text('Teacher')),
                                    DataColumn(label: Text('Note')),
                                    DataColumn(label: Text('Action')),
                                  ],
                                  rows: List.generate(
                                    ((_currentPage + 1) * _rowsPerPage >
                                            _filteredClasses.length)
                                        ? _filteredClasses.length -
                                              (_currentPage * _rowsPerPage)
                                        : _rowsPerPage,
                                    (index) {
                                      final actualIndex =
                                          (_currentPage * _rowsPerPage) + index;
                                      if (actualIndex >=
                                          _filteredClasses.length) {
                                        return const DataRow(
                                          cells: [
                                            DataCell(Text('')),
                                            DataCell(Text('')),
                                            DataCell(Text('')),
                                            DataCell(Text('')),
                                            DataCell(Text('')),
                                            DataCell(Text('')),
                                          ],
                                        );
                                      }

                                      final item =
                                          _filteredClasses[actualIndex];
                                      final classID = int.parse(
                                        (item['classesID'] ?? 0).toString(),
                                      );

                                      return DataRow(
                                        cells: [
                                          DataCell(
                                            Text((actualIndex + 1).toString()),
                                          ),
                                          DataCell(Text(item['classes'] ?? '')),
                                          DataCell(
                                            Text(item['classes_numeric'] ?? ''),
                                          ),
                                          DataCell(
                                            Text(
                                              (item['teacher_name'] ??
                                                      item['teacherID'] ??
                                                      'N/A')
                                                  .toString(),
                                            ),
                                          ),
                                          DataCell(
                                            Text(
                                              (item['note'] ?? '').toString(),
                                            ),
                                          ),
                                          DataCell(
                                            PopupMenuButton<String>(
                                              icon: const Icon(Icons.more_vert),
                                              onSelected: (value) {
                                                if (value == 'detail') {
                                                  Navigator.push(
                                                    context,
                                                    MaterialPageRoute(
                                                      builder: (context) =>
                                                          ClassDetailPage(
                                                            classID: classID,
                                                            userData:
                                                                widget.userData,
                                                          ),
                                                    ),
                                                  );
                                                } else if (value == 'edit') {
                                                  Navigator.push(
                                                    context,
                                                    MaterialPageRoute(
                                                      builder: (context) =>
                                                          ClassFormPage(
                                                            classData: item,
                                                            userData:
                                                                widget.userData,
                                                          ),
                                                    ),
                                                  ).then((value) {
                                                    if (value == true &&
                                                        _selectedCampus !=
                                                            null) {
                                                      _fetchClasses(
                                                        _selectedCampus!,
                                                      );
                                                    }
                                                  });
                                                } else if (value == 'delete') {
                                                  _deleteClass(classID);
                                                }
                                              },
                                              itemBuilder: (context) => [
                                                const PopupMenuItem(
                                                  value: 'detail',
                                                  child: Text('Detail'),
                                                ),
                                                const PopupMenuItem(
                                                  value: 'edit',
                                                  child: Text('Edit'),
                                                ),
                                                const PopupMenuItem(
                                                  value: 'delete',
                                                  child: Text('Delete'),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      );
                                    },
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
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
                                items: [5, 10, 20, 50].map((value) {
                                  return DropdownMenuItem<int>(
                                    value: value,
                                    child: Text('$value rows'),
                                  );
                                }).toList(),
                                onChanged: (newValue) {
                                  if (newValue == null) return;
                                  setState(() {
                                    _rowsPerPage = newValue;
                                    _currentPage = 0;
                                  });
                                },
                              ),
                              const SizedBox(width: 20),
                              Text(
                                '${(_currentPage * _rowsPerPage) + 1}-${((_currentPage + 1) * _rowsPerPage) > _filteredClasses.length ? _filteredClasses.length : (_currentPage + 1) * _rowsPerPage} of ${_filteredClasses.length}',
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
                                onPressed:
                                    ((_currentPage + 1) * _rowsPerPage <
                                        _filteredClasses.length)
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
              builder: (context) => ClassFormPage(userData: widget.userData),
            ),
          ).then((value) {
            if (value == true && _selectedCampus != null) {
              _fetchClasses(_selectedCampus!);
            }
          });
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
