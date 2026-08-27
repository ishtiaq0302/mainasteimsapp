import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as dev;
import 'package:first_app/config.dart';
import 'user_detail_page.dart';
import 'user_form_page.dart';

class UserListPage extends StatefulWidget {
  final Map userData;
  const UserListPage({super.key, required this.userData});

  @override
  State<UserListPage> createState() => _UserListPageState();
}

class _UserListPageState extends State<UserListPage> {
  List _users = [];
  List _filteredUsers = [];
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
    _searchController.addListener(_filterUsers);
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

  Future<void> _fetchUsers(String campusID) async {
    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/users';
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
        _users = result['data'] ?? [];
        _currentPage = 0;
        _filterUsers();
        _isLoading = false;
      });
    } catch (e) {
      dev.log('Fetch Users Error: $e');
      setState(() => _isLoading = false);
    }
  }

  void _filterUsers() {
    setState(() {
      String query = _searchController.text.toLowerCase();
      if (query.isEmpty) {
        _filteredUsers = _users;
      } else {
        _filteredUsers = _users.where((u) {
          final name = u['name'].toString().toLowerCase();
          final type = (u['usertype'] ?? '').toString().toLowerCase();
          final email = (u['email'] ?? '').toString().toLowerCase();
          return name.contains(query) || type.contains(query) || email.contains(query);
        }).toList();
      }
    });
  }

  Future<void> _toggleStatus(int id, bool status) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/user_status';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'userID': id,
          'status': status ? 1 : 0,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          final index = _users.indexWhere((u) => u['userID'].toString() == id.toString());
          if (index != -1) {
            _users[index]['active'] = status ? "1" : "0";
            _filterUsers();
          }
        });
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result['message'])));
      }
    } catch (e) {
      dev.log('Status Error: $e');
    }
  }

  Future<void> _deleteUser(int id) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/user_delete';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'userID': id,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      if (jsonDecode(response.body)['status'] == true) {
        if (_selectedCampus != null) _fetchUsers(_selectedCampus!);
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('User deleted')));
      }
    } catch (e) {
      dev.log('Delete Error: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('${AppConfig.appSettings['sname']} Users')),
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
                    if (v != null) _fetchUsers(v);
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
                    ? const Center(child: Text('Please select a campus to view users'))
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
                                        DataColumn(label: Text('Role')),
                                        DataColumn(label: Text('Status')),
                                        DataColumn(label: Text('Action')),
                                      ],
                                      rows: List.generate(
                                        ((_currentPage + 1) * _rowsPerPage > _filteredUsers.length)
                                            ? _filteredUsers.length - (_currentPage * _rowsPerPage)
                                            : _rowsPerPage,
                                        (index) {
                                          final actualIndex = (_currentPage * _rowsPerPage) + index;
                                          final u = _filteredUsers[actualIndex];
                                          final userID = int.parse(u['userID'].toString());
                                          final bool isActive = u['active'] == "1";
                                          String base = AppConfig.baseUrl;
                                          if (!base.endsWith('/')) base += '/';
                                          String photoUrl = '${base}uploads/images/${u['photo']}';

                                          return DataRow(cells: [
                                            DataCell(Text((actualIndex + 1).toString())),
                                            DataCell(CircleAvatar(
                                              radius: 15,
                                              backgroundImage: u['photo'] != null && u['photo'] != 'default.png' ? NetworkImage(photoUrl) : null,
                                              child: u['photo'] == null || u['photo'] == 'default.png' ? Text(u['name'][0].toUpperCase()) : null,
                                            )),
                                            DataCell(Text(u['name'])),
                                            DataCell(Text(u['email'] ?? 'N/A')),
                                            DataCell(Text(u['usertype'] ?? 'N/A')),
                                            DataCell(Switch(
                                              value: isActive,
                                              onChanged: (v) => _toggleStatus(userID, v),
                                              activeColor: Colors.green,
                                            )),
                                            DataCell(PopupMenuButton<String>(
                                              icon: const Icon(Icons.more_vert),
                                              onSelected: (v) {
                                                if (v == 'detail') {
                                                  Navigator.push(context, MaterialPageRoute(builder: (c) => UserDetailPage(userID: userID, userData: widget.userData)));
                                                }
                                                if (v == 'edit') {
                                                  Navigator.push(context, MaterialPageRoute(builder: (c) => UserFormPage(userDataForm: u, userData: widget.userData))).then((v) {
                                                    if (v == true) _fetchUsers(_selectedCampus!);
                                                  });
                                                }
                                                if (v == 'delete') _deleteUser(userID);
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
                                    '${(_currentPage * _rowsPerPage) + 1}-${((_currentPage + 1) * _rowsPerPage) > _filteredUsers.length ? _filteredUsers.length : (_currentPage + 1) * _rowsPerPage} of ${_filteredUsers.length}',
                                    style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                                  ),
                                  const Spacer(),
                                  IconButton(
                                    icon: const Icon(Icons.chevron_left),
                                    onPressed: _currentPage > 0 ? () => setState(() => _currentPage--) : null,
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.chevron_right),
                                    onPressed: (_currentPage + 1) * _rowsPerPage < _filteredUsers.length ? () => setState(() => _currentPage++) : null,
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
            MaterialPageRoute(builder: (c) => UserFormPage(userData: widget.userData))
          ).then((v) {
            if(v == true && _selectedCampus != null) _fetchUsers(_selectedCampus!);
          });
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
