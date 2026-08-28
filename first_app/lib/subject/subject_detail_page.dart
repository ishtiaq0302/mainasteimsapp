import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class SubjectDetailPage extends StatefulWidget {
  final int subjectID;
  final Map userData;

  const SubjectDetailPage({
    super.key,
    required this.subjectID,
    required this.userData,
  });

  @override
  State<SubjectDetailPage> createState() => _SubjectDetailPageState();
}

class _SubjectDetailPageState extends State<SubjectDetailPage> {
  Map? _subject;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/subject_view'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'subjectID': widget.subjectID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      if (result['status'] == true) {
        setState(() { _subject = result['data']; _isLoading = false; });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      dev.log('Subject detail error: $e');
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Widget _tile(IconData icon, String title, String? value) => ListTile(
        leading: Icon(icon, color: Colors.blueGrey),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(value?.isNotEmpty == true ? value! : '—'),
      );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Subject Details')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _subject == null
              ? const Center(child: Text('Failed to load subject details'))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    const Icon(Icons.book, size: 64, color: Colors.orange),
                    const SizedBox(height: 12),
                    Center(
                      child: Text(
                        _subject!['subject'] ?? 'Subject',
                        style: Theme.of(context).textTheme.headlineSmall,
                      ),
                    ),
                    Center(
                      child: Text(
                        _subject!['class_name'] ?? '',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey),
                      ),
                    ),
                    const Divider(height: 28),
                    _tile(Icons.code,          'Subject Code',   _subject!['subject_code']?.toString()),
                    _tile(Icons.category,      'Type',           _subject!['type']?.toString()),
                    _tile(Icons.grade,         'Pass Mark',      _subject!['passmark']?.toString()),
                    _tile(Icons.score,         'Final Mark',     _subject!['finalmark']?.toString()),
                    _tile(Icons.person,        'Author',         _subject!['subject_author']?.toString()),
                    _tile(Icons.school,        'Class',          _subject!['class_name']?.toString()),
                    _tile(Icons.location_city, 'Campus ID',      _subject!['campusID']?.toString()),
                    _tile(Icons.calendar_today,'Created',        _subject!['create_date']?.toString()),
                    _tile(Icons.edit_calendar, 'Modified',       _subject!['modify_date']?.toString()),
                  ],
                ),
    );
  }
}
