import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:http/http.dart' as http;

import 'dart:convert';
import 'dart:developer' as dev;
import 'dart:io';
import 'dart:typed_data';

import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:excel/excel.dart';
import 'package:csv/csv.dart';
import 'package:path_provider/path_provider.dart';
import 'package:printing/printing.dart';

import 'student_detail_page.dart';
import 'student_form_page.dart';

import 'package:first_app/config.dart';

class StudentListPage extends StatefulWidget {
  final Map userData;

  const StudentListPage({super.key, required this.userData});

  @override
  State<StudentListPage> createState() => _StudentListPageState();
}

class _StudentListPageState extends State<StudentListPage> {
  List _students = [];
  List _filteredStudents = [];
  List _sections = [];
  List _campuses = [];
  List _classes = [];

  String? _selectedCampus;
  String? _selectedClass;
  bool _isLoading = false;
  int _currentTabIndex = 0;
  int _rowsPerPage = 10;
  int _currentPage = 0;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchInitialMetadata();
    _searchController.addListener(_filterStudents);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchInitialMetadata() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/metadata';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': 0}),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _campuses = result['data']['campuses'];
        });
      }
    } catch (e) {
      dev.log('Metadata Error: $e');
    }
  }

  Future<void> _fetchClasses(String campusID) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/metadata';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': campusID}),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _classes = result['data']['classes'];
          _selectedClass = null;
          _students = [];
          _filteredStudents = [];
          _sections = [];
        });
      }
    } catch (e) {
      dev.log('Classes Error: $e');
    }
  }

  Future<void> _fetchStudentsAndSections(String classID) async {
    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String metadataUrl = '${base}api/metadata';
    final String listUrl = '${base}api/student_list';

    try {
      final metaRes = await http.post(
        Uri.parse(metadataUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'classesID': classID}),
      );
      final metaResult = jsonDecode(metaRes.body);

      final listRes = await http.post(
        Uri.parse(listUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'schoolyearID': widget.userData['defaultschoolyearID'],
          'campusID': _selectedCampus,
          'classesID': classID,
        }),
      );
      final listResult = jsonDecode(listRes.body);

      setState(() {
        _sections = metaResult['data']['sections'] ?? [];
        _students = listResult['data'] ?? [];
        _currentPage = 0; // Reset pagination on new fetch
        _filterStudents();
        _isLoading = false;
        _currentTabIndex = 0;
      });
    } catch (e) {
      dev.log('Fetch Error: $e');
      setState(() => _isLoading = false);
    }
  }

  void _filterStudents() {
    setState(() {
      List temp = _students;

      if (_currentTabIndex > 0) {
        final sectionID = _sections[_currentTabIndex - 1]['sectionID']
            .toString();
        temp = temp
            .where((s) => s['srsectionID'].toString() == sectionID)
            .toList();
      }

      String query = _searchController.text.toLowerCase();
      if (query.isNotEmpty) {
        temp = temp.where((s) {
          final name = s['srname'].toString().toLowerCase();
          final roll = s['srroll']?.toString().toLowerCase() ?? '';
          final email = s['email']?.toString().toLowerCase() ?? '';
          return name.contains(query) ||
              roll.contains(query) ||
              email.contains(query);
        }).toList();
      }
      _filteredStudents = temp;

      // Ensure current page is still valid after filtering
      if (_currentPage * _rowsPerPage >= _filteredStudents.length) {
        _currentPage = 0;
      }
    });
  }

  Future<void> _toggleStatus(int id, bool status) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/student_status';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'studentID': id, 'status': status ? 1 : 0}),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          final index = _students.indexWhere(
            (s) => s['srstudentID'].toString() == id.toString(),
          );
          if (index != -1) {
            _students[index]['active'] = status ? "1" : "0";
            _filterStudents();
          }
        });
        if (!mounted) return;
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(result['message'])));
      }
    } catch (e) {
      dev.log('Status Error: $e');
    }
  }

  Future<void> _deleteStudent(int id) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/student_delete';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'studentID': id}),
      );
      if (jsonDecode(response.body)['status'] == true) {
        _fetchStudentsAndSections(_selectedClass!);
        if (!mounted) return;
        ScaffoldMessenger.of(context)
            .showSnackBar(const SnackBar(content: Text('Student deleted')));
      }
    } catch (e) {
      dev.log('Delete Error: $e');
    }
  }

  Future<void> _exportPDF() async {
    final pdf = pw.Document();
    pdf.addPage(
      pw.Page(
        build: (pw.Context context) {
          return pw.Column(
            children: [
              pw.Header(level: 0, child: pw.Text('Student List')),
              pw.TableHelper.fromTextArray(
                context: context,
                data: <List<String>>[
                  <String>['#', 'Name', 'Roll', 'Email', 'Class', 'Section'],
                  ..._filteredStudents.asMap().entries.map(
                    (e) => [
                      (e.key + 1).toString(),
                      e.value['srname'],
                      e.value['srroll']?.toString() ?? 'N/A',
                      e.value['email'] ?? 'N/A',
                      e.value['srclasses'] ?? 'N/A',
                      e.value['srsection'] ?? 'N/A',
                    ],
                  ),
                ],
              ),
            ],
          );
        },
      ),
    );
    await Printing.layoutPdf(onLayout: (format) async => pdf.save());
  }

  Future<void> _exportExcel() async {
    var excel = Excel.createExcel();
    Sheet sheetObject = excel['Students'];

    sheetObject.appendRow([
      TextCellValue('#'),
      TextCellValue('Name'),
      TextCellValue('Roll'),
      TextCellValue('Email'),
      TextCellValue('Class'),
      TextCellValue('Section'),
    ]);

    for (var i = 0; i < _filteredStudents.length; i++) {
      final s = _filteredStudents[i];
      sheetObject.appendRow([
        IntCellValue(i + 1),
        TextCellValue(s['srname'] ?? ''),
        TextCellValue(s['srroll']?.toString() ?? ''),
        TextCellValue(s['email'] ?? ''),
        TextCellValue(s['srclasses'] ?? ''),
        TextCellValue(s['srsection'] ?? ''),
      ]);
    }

    final fileBytes = excel.save();
    if (fileBytes == null) return;

    if (kIsWeb) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Excel download is available in the web version only.'),
        ),
      );
      return;
    }

    final directory = await getApplicationDocumentsDirectory();
    final file = File('${directory.path}/students.xlsx')
      ..createSync(recursive: true)
      ..writeAsBytesSync(fileBytes);
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text('Excel saved to: ${file.path}')));
  }

  Future<void> _exportCSV() async {
    List<List<dynamic>> rows = [
      ["#", "Name", "Roll", "Email", "Class", "Section"],
    ];
    for (var i = 0; i < _filteredStudents.length; i++) {
      final s = _filteredStudents[i];
      rows.add([
        i + 1,
        s['srname'],
        s['srroll'],
        s['email'],
        s['srclasses'],
        s['srsection'],
      ]);
    }

    String csvData = rows.map((row) => row.join(',')).join('\n');

    if (kIsWeb) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('CSV download is available in the web version only.'),
        ),
      );
      return;
    }

    final directory = await getApplicationDocumentsDirectory();
    final file = File('${directory.path}/students.csv');
    await file.writeAsString(csvData);
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text('CSV saved to: ${file.path}')));
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      key: ValueKey(_selectedClass),
      length: _sections.length + 1,
      child: Scaffold(
        appBar: AppBar(
          title: Text('${AppConfig.appSettings['sname']} Students'),
          actions: [
            PopupMenuButton<String>(
              icon: const Icon(Icons.download),
              onSelected: (v) {
                if (v == 'pdf') _exportPDF();
                if (v == 'excel') _exportExcel();
                if (v == 'csv') _exportCSV();
              },
              itemBuilder: (c) => [
                const PopupMenuItem(value: 'pdf', child: Text('Export PDF')),
                const PopupMenuItem(
                  value: 'excel',
                  child: Text('Export Excel'),
                ),
                const PopupMenuItem(value: 'csv', child: Text('Export CSV')),
              ],
            ),
          ],
        ),
        body: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              color: Colors.grey.shade100,
              child: Column(
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: DropdownButtonFormField<String>(
                          decoration: const InputDecoration(
                            labelText: 'Campus',
                            border: OutlineInputBorder(),
                          ),
                          value: _selectedCampus,
                          items: _campuses
                              .map(
                                (c) => DropdownMenuItem<String>(
                                  value: c['campusID'].toString(),
                                  child: Text(c['name']),
                                ),
                              )
                              .toList(),
                          onChanged: (v) {
                            setState(() => _selectedCampus = v);
                            if (v != null) _fetchClasses(v);
                          },
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: DropdownButtonFormField<String>(
                          decoration: const InputDecoration(
                            labelText: 'Class',
                            border: OutlineInputBorder(),
                          ),
                          value: _selectedClass,
                          items: _classes
                              .map(
                                (c) => DropdownMenuItem<String>(
                                  value: c['classesID'].toString(),
                                  child: Text(c['classes']),
                                ),
                              )
                              .toList(),
                          onChanged: (v) {
                            setState(() => _selectedClass = v);
                            if (v != null) _fetchStudentsAndSections(v);
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      labelText: 'Search...',
                      prefixIcon: Icon(Icons.search),
                      border: OutlineInputBorder(),
                    ),
                  ),
                ],
              ),
            ),
            if (_selectedClass != null)
              TabBar(
                isScrollable: true,
                onTap: (index) {
                  setState(() => _currentTabIndex = index);
                  _filterStudents();
                },
                tabs: [
                  const Tab(text: 'All'),
                  ..._sections.map((s) => Tab(text: s['section'])),
                ],
              ),
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _selectedClass == null
                  ? const Center(child: Text('Select Campus and Class'))
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
                                      DataColumn(label: Text('Photo')),
                                      DataColumn(label: Text('Name')),
                                      DataColumn(label: Text('Roll')),
                                      DataColumn(label: Text('Email')),
                                      DataColumn(label: Text('Status')),
                                      DataColumn(label: Text('Action')),
                                    ],
                                    rows: List.generate(
                                      (_currentPage + 1) * _rowsPerPage >
                                              _filteredStudents.length
                                          ? _filteredStudents.length -
                                                (_currentPage * _rowsPerPage)
                                          : _rowsPerPage,
                                      (index) {
                                        final actualIndex =
                                            (_currentPage * _rowsPerPage) +
                                            index;
                                        final s =
                                            _filteredStudents[actualIndex];
                                        final studentID = int.parse(
                                          s['srstudentID'].toString(),
                                        );
                                        final bool isActive =
                                            s['active'] == "1";
                                        String base = AppConfig.baseUrl;
                                        if (!base.endsWith('/')) base += '/';
                                        String photoUrl =
                                            '${base}uploads/images/${s['photo']}';

                                        return DataRow(
                                          cells: [
                                            DataCell(
                                              Text(
                                                (actualIndex + 1).toString(),
                                              ),
                                            ),
                                            DataCell(
                                              CircleAvatar(
                                                radius: 15,
                                                backgroundImage:
                                                    s['photo'] != null &&
                                                        s['photo'] !=
                                                            'default.png'
                                                    ? NetworkImage(photoUrl)
                                                    : null,
                                                child:
                                                    s['photo'] == null ||
                                                        s['photo'] ==
                                                            'default.png'
                                                    ? Text(
                                                        s['srname'][0]
                                                            .toUpperCase(),
                                                      )
                                                    : null,
                                              ),
                                            ),
                                            DataCell(Text(s['srname'])),
                                            DataCell(
                                              Text(
                                                s['srroll']?.toString() ??
                                                    'N/A',
                                              ),
                                            ),
                                            DataCell(Text(s['email'] ?? 'N/A')),
                                            DataCell(
                                              Switch(
                                                value: isActive,
                                                onChanged: (v) =>
                                                    _toggleStatus(studentID, v),
                                                activeColor: Colors.green,
                                              ),
                                            ),
                                            DataCell(
                                              PopupMenuButton<String>(
                                                icon: const Icon(
                                                  Icons.more_vert,
                                                ),
                                                onSelected: (v) {
                                                  if (v == 'detail') {
                                                    Navigator.push(
                                                      context,
                                                      MaterialPageRoute(
                                                        builder: (c) =>
                                                            StudentDetailPage(
                                                              studentID:
                                                                  studentID,
                                                              userData: widget
                                                                  .userData,
                                                            ),
                                                      ),
                                                    );
                                                  }
                                                  if (v == 'edit') {
                                                    Navigator.push(
                                                      context,
                                                      MaterialPageRoute(
                                                        builder: (c) =>
                                                            StudentFormPage(
                                                              studentData: s,
                                                              userData: widget
                                                                  .userData,
                                                            ),
                                                      ),
                                                    ).then(
                                                      (_) =>
                                                          _fetchStudentsAndSections(
                                                            _selectedClass!,
                                                          ),
                                                    );
                                                  }
                                                  if (v == 'delete')
                                                    _deleteStudent(studentID);
                                                },
                                                itemBuilder: (c) => [
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
                          // Custom Footer with Dropdown on Left
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
                                  items: [5, 10, 20, 50].map((int value) {
                                    return DropdownMenuItem<int>(
                                      value: value,
                                      child: Text('$value rows'),
                                    );
                                  }).toList(),
                                  onChanged: (int? newValue) {
                                    if (newValue != null) {
                                      setState(() {
                                        _rowsPerPage = newValue;
                                        _currentPage = 0;
                                      });
                                    }
                                  },
                                ),
                                const SizedBox(width: 20),
                                Text(
                                  '${(_currentPage * _rowsPerPage) + 1}-${((_currentPage + 1) * _rowsPerPage) > _filteredStudents.length ? _filteredStudents.length : (_currentPage + 1) * _rowsPerPage} of ${_filteredStudents.length}',
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
                                      (_currentPage + 1) * _rowsPerPage <
                                          _filteredStudents.length
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
          onPressed: () =>
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) =>
                      StudentFormPage(userData: widget.userData),
                ),
              ).then((_) {
                if (_selectedClass != null)
                  _fetchStudentsAndSections(_selectedClass!);
              }),
          child: const Icon(Icons.add),
        ),
      ),
    );
  }
}
