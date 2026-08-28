import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';

import 'package:first_app/config.dart';
import 'syllabus_form_page.dart';

class SyllabusListPage extends StatefulWidget {
  final Map userData;

  const SyllabusListPage({super.key, required this.userData});

  @override
  State<SyllabusListPage> createState() => _SyllabusListPageState();
}

class _SyllabusListPageState extends State<SyllabusListPage> {
  List _syllabuses   = [];
  List _filtered     = [];
  List _campuses     = [];
  List _classes      = [];
  List _schoolyears  = [];

  String? _selCampus;
  String? _selClass;
  String? _selSchoolyear;

  bool _isLoading   = false;
  int  _rowsPerPage = 10;
  int  _currentPage = 0;

  final TextEditingController _searchCtrl = TextEditingController();

  static const Color _primary = Color(0xFF4A148C); // deep purple
  static const Color _accent  = Color(0xFFAB47BC);

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

  // ── Meta ──────────────────────────────────────────────────────────────────
  Future<void> _fetchMeta() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
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
      dev.log('Syllabus meta error: $e');
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
        _classes   = result['data'] ?? [];
        _selClass  = null;
        _syllabuses = [];
        _filtered  = [];
      });
    } catch (e) {
      dev.log('Syllabus fetch classes error: $e');
    }
  }

  Future<void> _fetchSyllabuses() async {
    setState(() => _isLoading = true);
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final body = <String, dynamic>{
        'adminID': widget.userData['adminID'] ?? 1,
      };
      if (_selCampus    != null) body['campusID']     = _selCampus;
      if (_selClass     != null) body['classesID']    = _selClass;
      if (_selSchoolyear != null) body['schoolyearID'] = _selSchoolyear;

      final res = await http.post(
        Uri.parse('${base}api/syllabus'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );
      final result = jsonDecode(res.body);
      if (!mounted) return;
      setState(() {
        _syllabuses  = result['data'] ?? [];
        _currentPage = 0;
        _filter();
        _isLoading = false;
      });
    } catch (e) {
      dev.log('Fetch syllabuses error: $e');
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _delete(int id) async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/syllabus_delete'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'syllabusID': id, 'adminID': widget.userData['adminID'] ?? 1}),
      );
      final result = jsonDecode(res.body);
      if (!mounted) return;
      if (result['status'] == true) {
        _fetchSyllabuses();
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Syllabus deleted')));
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result['message'] ?? 'Delete failed')));
      }
    } catch (e) {
      dev.log('Delete syllabus error: $e');
    }
  }

  Future<void> _downloadFile(String? fileUrl) async {
    if (fileUrl == null || fileUrl.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('No file available for download')));
      return;
    }
    try {
      final uri = Uri.parse(fileUrl);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Could not open download link')));
      }
    } catch (e) {
      dev.log('Download file error: $e');
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error launching file: $e')));
    }
  }

  void _filter() {
    final q = _searchCtrl.text.trim().toLowerCase();
    setState(() {
      _filtered = q.isEmpty
          ? _syllabuses
          : _syllabuses.where((s) {
              final title = (s['title']       ?? '').toString().toLowerCase();
              final cls   = (s['class_name']  ?? '').toString().toLowerCase();
              final desc  = (s['description'] ?? '').toString().toLowerCase();
              final up    = (s['uploader']    ?? '').toString().toLowerCase();
              return title.contains(q) || cls.contains(q) || desc.contains(q) || up.contains(q);
            }).toList();
    });
  }

  void _confirmDelete(int id, String title) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Delete Syllabus'),
        content: Text('Delete "$title"? The uploaded file will also be removed.'),
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

  InputDecoration _dropDecor(String label, IconData icon) => InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
        isDense: true,
        prefixIcon: Icon(icon, size: 20),
      );

  @override
  Widget build(BuildContext context) {
    final totalPages = (_filtered.length / _rowsPerPage).ceil().clamp(1, 9999);
    final startIdx   = _currentPage * _rowsPerPage;
    final endIdx     = (startIdx + _rowsPerPage).clamp(0, _filtered.length);
    final pageItems  = _filtered.isEmpty ? [] : _filtered.sublist(startIdx, endIdx);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Syllabus'),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [_primary, _accent]),
          ),
        ),
        foregroundColor: Colors.white,
      ),

      body: Column(
        children: [
          // ── Filter bar ──────────────────────────────────────────────────
          Container(
            padding: const EdgeInsets.all(12),
            color: Colors.grey.shade100,
            child: Column(
              children: [
                // Campus
                DropdownButtonFormField<String>(
                  value: _selCampus,
                  decoration: _dropDecor('Campus', Icons.location_city),
                  items: _campuses.map((c) => DropdownMenuItem<String>(
                        value: c['campusID'].toString(),
                        child: Text(c['name'] ?? 'Campus'),
                      )).toList(),
                  onChanged: (v) {
                    setState(() {
                      _selCampus    = v;
                      _selClass     = null;
                      _classes      = [];
                      _syllabuses   = [];
                      _filtered     = [];
                    });
                    if (v != null) _fetchClasses(v);
                    _fetchSyllabuses();
                  },
                ),

                if (_classes.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  DropdownButtonFormField<String>(
                    value: _selClass,
                    decoration: _dropDecor('Class (optional)', Icons.class_),
                    items: [
                      const DropdownMenuItem<String>(value: null, child: Text('All Classes')),
                      ..._classes.map((c) => DropdownMenuItem<String>(
                            value: c['classesID'].toString(),
                            child: Text(c['classes'] ?? 'Class'),
                          )),
                    ],
                    onChanged: (v) {
                      setState(() => _selClass = v);
                      _fetchSyllabuses();
                    },
                  ),
                ],

                if (_schoolyears.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  DropdownButtonFormField<String>(
                    value: _selSchoolyear,
                    decoration: _dropDecor('School Year (optional)', Icons.calendar_month),
                    items: [
                      const DropdownMenuItem<String>(value: null, child: Text('All Years')),
                      ..._schoolyears.map((sy) => DropdownMenuItem<String>(
                            value: sy['schoolyearID'].toString(),
                            child: Text(sy['year'] ?? 'Year'),
                          )),
                    ],
                    onChanged: (v) {
                      setState(() => _selSchoolyear = v);
                      _fetchSyllabuses();
                    },
                  ),
                ],

                const SizedBox(height: 8),
                TextField(
                  controller: _searchCtrl,
                  decoration: InputDecoration(
                    hintText: 'Search subject, uploader, description…',
                    prefixIcon: const Icon(Icons.search),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    isDense: true,
                    filled: true,
                    fillColor: Colors.white,
                  ),
                ),
              ],
            ),
          ),

          // Stats chip
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            child: Row(children: [
              Chip(
                backgroundColor: _primary.withOpacity(0.1),
                avatar: const Icon(Icons.menu_book, color: _primary, size: 18),
                label: Text(
                  '${_filtered.length} syllabus${_filtered.length == 1 ? '' : 'es'}',
                  style: const TextStyle(color: _primary, fontWeight: FontWeight.w600),
                ),
              ),
            ]),
          ),

          // ── Table ─────────────────────────────────────────────────────────
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _syllabuses.isEmpty && _selCampus == null
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.menu_book, size: 64, color: Colors.grey.shade400),
                            const SizedBox(height: 12),
                            Text('Select filters to view syllabuses', style: TextStyle(color: Colors.grey.shade600)),
                          ],
                        ),
                      )
                    : _filtered.isEmpty
                        ? Center(child: Text('No syllabuses found', style: TextStyle(color: Colors.grey.shade600)))
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
                                            DataColumn(label: Text('#',            style: TextStyle(fontWeight: FontWeight.bold))),
                                            DataColumn(label: Text('Subject Name', style: TextStyle(fontWeight: FontWeight.bold))),
                                            DataColumn(label: Text('Description',  style: TextStyle(fontWeight: FontWeight.bold))),
                                            DataColumn(label: Text('Date',         style: TextStyle(fontWeight: FontWeight.bold))),
                                            DataColumn(label: Text('Uploader',     style: TextStyle(fontWeight: FontWeight.bold))),
                                            DataColumn(label: Text('File',         style: TextStyle(fontWeight: FontWeight.bold))),
                                            DataColumn(label: Text('Action',       style: TextStyle(fontWeight: FontWeight.bold))),
                                          ],
                                          rows: List.generate(pageItems.length, (i) {
                                            final item       = pageItems[i];
                                            final syllabusID = int.tryParse((item['syllabusID'] ?? '0').toString()) ?? 0;
                                            final title      = item['title'] ?? '';
                                            final fileUrl    = item['file_url'];

                                            return DataRow(cells: [
                                              DataCell(Text('${startIdx + i + 1}')),
                                              DataCell(Text(title, style: const TextStyle(fontWeight: FontWeight.w500))),
                                              DataCell(Text(item['description'] ?? '—')),
                                              DataCell(Text(item['date']        ?? '—')),
                                              DataCell(Text(item['uploader']    ?? 'Admin')),
                                              DataCell(
                                                item['originalfile'] != null && item['originalfile'] != ''
                                                    ? InkWell(
                                                        onTap: () => _downloadFile(fileUrl),
                                                        child: Row(
                                                          mainAxisSize: MainAxisSize.min,
                                                          children: [
                                                            const Icon(Icons.attach_file, size: 16, color: _primary),
                                                            const SizedBox(width: 4),
                                                            Flexible(
                                                              child: Text(
                                                                item['originalfile'] ?? '',
                                                                overflow: TextOverflow.ellipsis,
                                                                style: const TextStyle(fontSize: 12, color: _primary, decoration: TextDecoration.underline),
                                                              ),
                                                            ),
                                                          ],
                                                        ),
                                                      )
                                                    : const Text('—'),
                                              ),
                                              DataCell(
                                                PopupMenuButton<String>(
                                                  icon: const Icon(Icons.more_vert),
                                                  onSelected: (val) {
                                                    if (val == 'download') {
                                                      _downloadFile(fileUrl);
                                                    } else if (val == 'edit') {
                                                      Navigator.push(context, MaterialPageRoute(
                                                        builder: (_) => SyllabusFormPage(syllabusData: item, userData: widget.userData),
                                                      )).then((ok) { if (ok == true) _fetchSyllabuses(); });
                                                    } else if (val == 'delete') {
                                                      _confirmDelete(syllabusID, title);
                                                    }
                                                  },
                                                  itemBuilder: (_) => const [
                                                    PopupMenuItem(
                                                      value: 'download',
                                                      child: Row(
                                                        children: [
                                                          Icon(Icons.download, size: 18, color: Colors.blue),
                                                          SizedBox(width: 8),
                                                          Text('Download'),
                                                        ],
                                                      ),
                                                    ),
                                                    PopupMenuItem(
                                                      value: 'edit',
                                                      child: Row(
                                                        children: [
                                                          Icon(Icons.edit, size: 18, color: Colors.amber),
                                                          SizedBox(width: 8),
                                                          Text('Edit'),
                                                        ],
                                                      ),
                                                    ),
                                                    PopupMenuItem(
                                                      value: 'delete',
                                                      child: Row(
                                                        children: [
                                                          Icon(Icons.delete, size: 18, color: Colors.red),
                                                          SizedBox(width: 8),
                                                          Text('Delete'),
                                                        ],
                                                      ),
                                                    ),
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
            builder: (_) => SyllabusFormPage(
              userData: widget.userData,
              preselectedCampusID:     _selCampus,
              preselectedClassesID:    _selClass,
              preselectedSchoolyearID: _selSchoolyear,
            ),
          )).then((ok) { if (ok == true) _fetchSyllabuses(); });
        },
        backgroundColor: _primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Add Syllabus', style: TextStyle(color: Colors.white)),
      ),
    );
  }
}
