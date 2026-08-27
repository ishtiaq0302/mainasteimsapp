import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as dev;
import 'package:first_app/config.dart';
import 'parent_detail_page.dart';
import 'parent_form_page.dart';

class ParentListPage extends StatefulWidget {
  final Map userData;
  const ParentListPage({super.key, required this.userData});

  @override
  State<ParentListPage> createState() => _ParentListPageState();
}

class _ParentListPageState extends State<ParentListPage> {
  List _parents = [];
  List _filteredParents = [];
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
    _searchController.addListener(_filterParents);
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
          _campuses = result['data']['campuses'];
        });
      }
    } catch (e) {
      dev.log('Campuses Error: $e');
    }
  }

  Future<void> _fetchParents(String campusID) async {
    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/parents';
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
      setState(() {
        _parents = result['data'] ?? [];
        _currentPage = 0;
        _filterParents();
        _isLoading = false;
      });
    } catch (e) {
      dev.log('Fetch Parents Error: $e');
      setState(() => _isLoading = false);
    }
  }

  void _filterParents() {
    setState(() {
      String query = _searchController.text.toLowerCase();
      if (query.isEmpty) {
        _filteredParents = _parents;
      } else {
        _filteredParents = _parents.where((p) {
          final name = p['name'].toString().toLowerCase();
          final email = (p['email'] ?? '').toString().toLowerCase();
          final phone = (p['phone'] ?? '').toString().toLowerCase();
          return name.contains(query) || email.contains(query) || phone.contains(query);
        }).toList();
      }
    });
  }

  Future<void> _toggleStatus(int id, bool status) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/parent_status';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'parentsID': id,
          'status': status ? 1 : 0,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          final index = _parents.indexWhere((p) => p['parentsID'].toString() == id.toString());
          if (index != -1) {
            _parents[index]['active'] = status ? "1" : "0";
            _filterParents();
          }
        });
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result['message'])));
      }
    } catch (e) {
      dev.log('Status Error: $e');
    }
  }

  Future<void> _deleteParent(int id) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/parent_delete';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'parentsID': id,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      if (jsonDecode(response.body)['status'] == true) {
        if (_selectedCampus != null) _fetchParents(_selectedCampus!);
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Parent deleted')));
      }
    } catch (e) {
      dev.log('Delete Error: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('${AppConfig.appSettings['sname']} Parents')),
      body: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            color: Colors.grey.shade100,
            child: Column(
              children: [
                DropdownButtonFormField<String>(
                  decoration: const InputDecoration(labelText: 'Select Campus', border: OutlineInputBorder()),
                  value: _selectedCampus,
                  items: _campuses.map((c) => DropdownMenuItem<String>(value: c['campusID'].toString(), child: Text(c['name']))).toList(),
                  onChanged: (v) {
                    setState(() => _selectedCampus = v);
                    if (v != null) _fetchParents(v);
                  },
                ),
                if (_selectedCampus != null) ...[
                  const SizedBox(height: 10),
                  TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(labelText: 'Search...', prefixIcon: Icon(Icons.search), border: OutlineInputBorder()),
                  ),
                ],
              ],
            ),
          ),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _selectedCampus == null
                    ? const Center(child: Text('Please select a campus to view parents'))
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
                                      columnSpacing: 20,
                                      columns: const [
                                        DataColumn(label: Text('#')),
                                        DataColumn(label: Text('Photo')),
                                        DataColumn(label: Text('Name')),
                                        DataColumn(label: Text('Email')),
                                        DataColumn(label: Text('Status')),
                                        DataColumn(label: Text('Action')),
                                      ],
                                      rows: List.generate(
                                        ((_currentPage + 1) * _rowsPerPage > _filteredParents.length)
                                            ? _filteredParents.length - (_currentPage * _rowsPerPage)
                                            : _rowsPerPage,
                                        (index) {
                                          final actualIndex = (_currentPage * _rowsPerPage) + index;
                                          final p = _filteredParents[actualIndex];
                                          final parentsID = int.parse(p['parentsID'].toString());
                                          final bool isActive = p['active'] == "1";
                                          String base = AppConfig.baseUrl;
                                          if (!base.endsWith('/')) base += '/';
                                          String photoUrl = '${base}uploads/images/${p['photo']}';

                                          return DataRow(cells: [
                                            DataCell(Text((actualIndex + 1).toString())),
                                            DataCell(CircleAvatar(
                                              radius: 15,
                                              backgroundImage: p['photo'] != null && p['photo'] != 'default.png' ? NetworkImage(photoUrl) : null,
                                              child: p['photo'] == null || p['photo'] == 'default.png' ? Text(p['name'][0].toUpperCase()) : null,
                                            )),
                                            DataCell(Text(p['name'])),
                                            DataCell(Text(p['email'] ?? 'N/A')),
                                            DataCell(Switch(
                                              value: isActive,
                                              onChanged: (v) => _toggleStatus(parentsID, v),
                                              activeColor: Colors.green,
                                            )),
                                            DataCell(PopupMenuButton<String>(
                                              icon: const Icon(Icons.more_vert),
                                              onSelected: (v) {
                                                if (v == 'detail') {
                                                  Navigator.push(
                                                    context,
                                                    MaterialPageRoute(
                                                      builder: (c) => ParentDetailPage(
                                                        parentsID: parentsID,
                                                        userData: widget.userData,
                                                      ),
                                                    ),
                                                  );
                                                }
                                                if (v == 'edit') {
                                                  Navigator.push(
                                                    context,
                                                    MaterialPageRoute(
                                                      builder: (c) => ParentFormPage(
                                                        parentData: p,
                                                        userData: widget.userData,
                                                      ),
                                                    ),
                                                  ).then((v) {
                                                    if (v == true) _fetchParents(_selectedCampus!);
                                                  });
                                                }
                                                if (v == 'delete') _deleteParent(parentsID);
                                              },
                                              itemBuilder: (c) => [
                                                const PopupMenuItem(value: 'detail', child: Text('Detail')),
                                                const PopupMenuItem(value: 'edit', child: Text('Edit')),
                                                const PopupMenuItem(value: 'delete', child: Text('Delete')),
                                              ],
                                            )),
                                          ]);
                                        },
                                      ),
                                    ),
                                  ),
                                );
                              },
                            ),
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
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
                                    '${(_currentPage * _rowsPerPage) + 1}-${((_currentPage + 1) * _rowsPerPage) > _filteredParents.length ? _filteredParents.length : (_currentPage + 1) * _rowsPerPage} of ${_filteredParents.length}',
                                    style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                                  ),
                                  const Spacer(),
                                  IconButton(
                                    icon: const Icon(Icons.chevron_left),
                                    onPressed: _currentPage > 0 ? () => setState(() => _currentPage--) : null,
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.chevron_right),
                                    onPressed: (_currentPage + 1) * _rowsPerPage < _filteredParents.length ? () => setState(() => _currentPage++) : null,
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
            MaterialPageRoute(builder: (c) => ParentFormPage(userData: widget.userData)),
          ).then((v) {
            if(v == true && _selectedCampus != null) _fetchParents(_selectedCampus!);
          });
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
