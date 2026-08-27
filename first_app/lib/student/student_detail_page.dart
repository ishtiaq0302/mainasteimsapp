import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as dev;
import 'package:first_app/config.dart';

class StudentDetailPage extends StatefulWidget {
  final int studentID;
  final Map userData;

  const StudentDetailPage({super.key, required this.studentID, required this.userData});

  @override
  State<StudentDetailPage> createState() => _StudentDetailPageState();
}

class _StudentDetailPageState extends State<StudentDetailPage> {
  Map? _student;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/student_view';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'studentID': widget.studentID,
          'schoolyearID': widget.userData['defaultschoolyearID'],
        }),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _student = result['data'];
          _isLoading = false;
        });
      }
    } catch (e) {
      dev.log('Detail Error: $e');
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Student Profile')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _student == null
              ? const Center(child: Text('Failed to load profile'))
              : Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    children: [
                      const CircleAvatar(radius: 50, child: Icon(Icons.person, size: 50)),
                      const SizedBox(height: 16),
                      Text(_student!['name'] ?? '', style: Theme.of(context).textTheme.headlineMedium),
                      const Divider(),
                      ListTile(title: const Text('Email'), subtitle: Text(_student!['email'] ?? 'N/A')),
                      ListTile(title: const Text('Class'), subtitle: Text(_student!['srclasses'] ?? 'N/A')),
                      ListTile(title: const Text('Section'), subtitle: Text(_student!['srsection'] ?? 'N/A')),
                      ListTile(title: const Text('Roll'), subtitle: Text(_student!['srroll']?.toString() ?? 'N/A')),
                    ],
                  ),
                ),
    );
  }
}
