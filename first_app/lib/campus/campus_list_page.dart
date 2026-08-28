import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';
import 'campus_form_page.dart';

class CampusListPage extends StatefulWidget {
  final Map userData;

  const CampusListPage({super.key, required this.userData});

  @override
  State<CampusListPage> createState() => _CampusListPageState();
}

class _CampusListPageState extends State<CampusListPage> {
  List _campuses = [];
  List _filtered = [];

  bool _isLoading = false;
  int _rowsPerPage = 10;
  int _currentPage = 0;

  final TextEditingController _searchCtrl = TextEditingController();

  static const Color _primary   = Color(0xFF1565C0);
  static const Color _accent    = Color(0xFF42A5F5);

  @override
  void initState() {
    super.initState();
    _fetchCampuses();
    _searchCtrl.addListener(_filter);
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  // ── API ───────────────────────────────────────────────────────────────
  Future<void> _fetchCampuses() async {
    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/campus'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'adminID': widget.userData['adminID'] ?? 1}),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      setState(() {
        _campuses  = result['data'] ?? [];
        _currentPage = 0;
        _filter();
        _isLoading = false;
      });
    } catch (e) {
      dev.log('Fetch campuses error: $e');
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _delete(int id) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/campus_delete'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': id, 'adminID': widget.userData['adminID'] ?? 1}),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      if (result['status'] == true) {
        _fetchCampuses();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Campus deleted')),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Delete failed')),
        );
      }
    } catch (e) {
      dev.log('Delete campus error: $e');
    }
  }

  // ── Search/filter ─────────────────────────────────────────────────────
  void _filter() {
    final q = _searchCtrl.text.trim().toLowerCase();
    setState(() {
      _filtered = q.isEmpty
          ? _campuses
          : _campuses.where((c) {
              final name = (c['name'] ?? '').toString().toLowerCase();
              return name.contains(q);
            }).toList();
    });
  }

  // ── Confirm delete dialog ─────────────────────────────────────────────
  void _confirmDelete(int id, String name) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Delete Campus'),
        content: Text('Delete "$name"? This cannot be undone.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              _delete(id);
            },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  // ── Build ─────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final totalPages = (_filtered.length / _rowsPerPage).ceil().clamp(1, 9999);
    final startIdx   = _currentPage * _rowsPerPage;
    final endIdx     = (startIdx + _rowsPerPage).clamp(0, _filtered.length);
    final pageItems  = _filtered.isEmpty ? [] : _filtered.sublist(startIdx, endIdx);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Campuses'),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [_primary, _accent]),
          ),
        ),
        foregroundColor: Colors.white,
      ),

      body: Column(
        children: [
          // ── Search bar ────────────────────────────────────────────────
          Container(
            padding: const EdgeInsets.all(12),
            color: Colors.grey.shade100,
            child: TextField(
              controller: _searchCtrl,
              decoration: InputDecoration(
                hintText: 'Search campus…',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                filled: true,
                fillColor: Colors.white,
                isDense: true,
                contentPadding: const EdgeInsets.symmetric(vertical: 10),
              ),
            ),
          ),

          // ── Stats chip ────────────────────────────────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            child: Row(
              children: [
                Chip(
                  backgroundColor: _primary.withOpacity(0.1),
                  label: Text(
                    '${_filtered.length} campus${_filtered.length == 1 ? '' : 'es'}',
                    style: const TextStyle(color: _primary, fontWeight: FontWeight.w600),
                  ),
                  avatar: const Icon(Icons.location_city, color: _primary, size: 18),
                ),
              ],
            ),
          ),

          // ── Table / empty state ───────────────────────────────────────
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _filtered.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.location_city, size: 64, color: Colors.grey.shade400),
                            const SizedBox(height: 12),
                            Text('No campuses found', style: TextStyle(color: Colors.grey.shade600)),
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
                                      columnSpacing: 24,
                                      columns: const [
                                        DataColumn(label: Text('#',          style: TextStyle(fontWeight: FontWeight.bold))),
                                        DataColumn(label: Text('Campus Name',style: TextStyle(fontWeight: FontWeight.bold))),
                                        DataColumn(label: Text('Actions',    style: TextStyle(fontWeight: FontWeight.bold))),
                                      ],
                                      rows: List.generate(pageItems.length, (i) {
                                        final item     = pageItems[i];
                                        final campusID = int.tryParse((item['campusID'] ?? '0').toString()) ?? 0;
                                        final name     = item['name'] ?? '';

                                        return DataRow(cells: [
                                          DataCell(Text('${startIdx + i + 1}')),
                                          DataCell(
                                            Row(children: [
                                              const Icon(Icons.location_city, size: 18, color: _accent),
                                              const SizedBox(width: 6),
                                              Text(name, style: const TextStyle(fontWeight: FontWeight.w500)),
                                            ]),
                                          ),
                                          DataCell(
                                            PopupMenuButton<String>(
                                              icon: const Icon(Icons.more_vert),
                                              onSelected: (val) {
                                                if (val == 'edit') {
                                                  Navigator.push(context, MaterialPageRoute(
                                                    builder: (_) => CampusFormPage(campusData: item, userData: widget.userData),
                                                  )).then((ok) { if (ok == true) _fetchCampuses(); });
                                                } else if (val == 'delete') {
                                                  _confirmDelete(campusID, name);
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
            builder: (_) => CampusFormPage(userData: widget.userData),
          )).then((ok) { if (ok == true) _fetchCampuses(); });
        },
        backgroundColor: _primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Add Campus', style: TextStyle(color: Colors.white)),
      ),
    );
  }
}
