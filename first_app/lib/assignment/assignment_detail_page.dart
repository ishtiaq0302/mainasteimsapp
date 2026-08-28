import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class AssignmentDetailPage extends StatefulWidget {
  final int assignmentID;
  final Map userData;

  const AssignmentDetailPage({
    super.key,
    required this.assignmentID,
    required this.userData,
  });

  @override
  State<AssignmentDetailPage> createState() => _AssignmentDetailPageState();
}

class _AssignmentDetailPageState extends State<AssignmentDetailPage> {
  Map? _assignment;
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
        Uri.parse('${base}api/assignment_view'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'assignmentID': widget.assignmentID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      if (result['status'] == true) {
        setState(() { _assignment = result['data']; _isLoading = false; });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      dev.log('Assignment detail error: $e');
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
      appBar: AppBar(title: const Text('Assignment Details')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _assignment == null
              ? const Center(child: Text('Failed to load assignment details'))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    const Icon(Icons.assignment, size: 64, color: Colors.teal),
                    const SizedBox(height: 12),
                    Center(
                      child: Text(
                        _assignment!['title'] ?? 'Assignment',
                        style: Theme.of(context).textTheme.headlineSmall,
                        textAlign: TextAlign.center,
                      ),
                    ),
                    Center(
                      child: Text(
                        _assignment!['class_name'] ?? '',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey),
                      ),
                    ),
                    const Divider(height: 28),
                    _tile(Icons.description,      'Description',   _assignment!['description']?.toString()),
                    _tile(Icons.book,             'Subject',       _assignment!['subject_name']?.toString()),
                    _tile(Icons.school,           'Class',         _assignment!['class_name']?.toString()),
                    _tile(Icons.event,            'Deadline',      _assignment!['deadlinedate']?.toString()),
                    _tile(Icons.calendar_today,   'Date',          _assignment!['date']?.toString()),
                    _tile(Icons.location_city,    'Campus ID',     _assignment!['campusID']?.toString()),
                    _tile(Icons.file_present,     'File',          _assignment!['originalfile']?.toString()),
                  ],
                ),
    );
  }
}
