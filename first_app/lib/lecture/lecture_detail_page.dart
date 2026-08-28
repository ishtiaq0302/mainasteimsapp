import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class LectureDetailPage extends StatefulWidget {
  final int lectureID;
  final Map userData;

  const LectureDetailPage({
    super.key,
    required this.lectureID,
    required this.userData,
  });

  @override
  State<LectureDetailPage> createState() => _LectureDetailPageState();
}

class _LectureDetailPageState extends State<LectureDetailPage> {
  Map? _lecture;
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
        Uri.parse('${base}api/lecture_view'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'lectureID': widget.lectureID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      if (result['status'] == true) {
        setState(() { _lecture = result['data']; _isLoading = false; });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      dev.log('Lecture detail error: $e');
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
      appBar: AppBar(title: const Text('Lecture Details')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _lecture == null
              ? const Center(child: Text('Failed to load lecture details'))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    const Icon(Icons.video_library, size: 64, color: Colors.deepPurple),
                    const SizedBox(height: 12),
                    Center(
                      child: Text(
                        _lecture!['title'] ?? 'Lecture',
                        style: Theme.of(context).textTheme.headlineSmall,
                        textAlign: TextAlign.center,
                      ),
                    ),
                    Center(
                      child: Text(
                        _lecture!['class_name'] ?? '',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey),
                      ),
                    ),
                    const Divider(height: 28),
                    _tile(Icons.description,   'Description',   _lecture!['description']?.toString()),
                    _tile(Icons.calendar_today,'Date',          _lecture!['date']?.toString()),
                    _tile(Icons.school,        'Class',         _lecture!['class_name']?.toString()),
                    _tile(Icons.location_city, 'Campus ID',     _lecture!['campusID']?.toString()),
                    _tile(Icons.file_present,  'File',          _lecture!['originalfile']?.toString()),
                  ],
                ),
    );
  }
}
