import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class ClassDetailPage extends StatefulWidget {
  final int classID;
  final Map userData;

  const ClassDetailPage({
    super.key,
    required this.classID,
    required this.userData,
  });

  @override
  State<ClassDetailPage> createState() => _ClassDetailPageState();
}

class _ClassDetailPageState extends State<ClassDetailPage> {
  Map? _class;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/class_view';

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'classesID': widget.classID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );

      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _class = result['data'];
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      dev.log('Class detail error: $e');
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Class Details')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _class == null
          ? const Center(child: Text('Failed to load class details'))
          : Padding(
              padding: const EdgeInsets.all(16),
              child: ListView(
                children: [
                  const Icon(Icons.class_, size: 64, color: Colors.blue),
                  const SizedBox(height: 16),
                  Center(
                    child: Text(
                      _class!['classes'] ?? 'Class',
                      style: Theme.of(context).textTheme.headlineSmall,
                    ),
                  ),
                  const Divider(),
                  ListTile(
                    leading: const Icon(Icons.numbers),
                    title: const Text('Class Number'),
                    subtitle: Text(_class!['classes_numeric'] ?? 'N/A'),
                  ),
                  ListTile(
                    leading: const Icon(Icons.school),
                    title: const Text('Campus ID'),
                    subtitle: Text(_class!['campusID']?.toString() ?? 'N/A'),
                  ),
                  ListTile(
                    leading: const Icon(Icons.person),
                    title: const Text('Teacher ID'),
                    subtitle: Text(_class!['teacherID']?.toString() ?? 'N/A'),
                  ),
                  ListTile(
                    leading: const Icon(Icons.note),
                    title: const Text('Note'),
                    subtitle: Text(_class!['note'] ?? 'No note available'),
                  ),
                ],
              ),
            ),
    );
  }
}
