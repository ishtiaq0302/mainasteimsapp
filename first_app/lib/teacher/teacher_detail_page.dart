import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as dev;
import 'package:first_app/config.dart';

class TeacherDetailPage extends StatefulWidget {
  final int teacherID;
  final Map userData;

  const TeacherDetailPage({super.key, required this.teacherID, required this.userData});

  @override
  State<TeacherDetailPage> createState() => _TeacherDetailPageState();
}

class _TeacherDetailPageState extends State<TeacherDetailPage> {
  Map? _teacher;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/teacher_view';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'teacherID': widget.teacherID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _teacher = result['data'];
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
      appBar: AppBar(title: const Text('Teacher Profile')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _teacher == null
              ? const Center(child: Text('Failed to load profile'))
              : Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    children: [
                      const CircleAvatar(radius: 50, child: Icon(Icons.person, size: 50)),
                      const SizedBox(height: 16),
                      Text(_teacher!['name'] ?? '', style: Theme.of(context).textTheme.headlineMedium),
                      Text(_teacher!['designation'] ?? 'Teacher', style: const TextStyle(fontSize: 18, color: Colors.grey)),
                      const Divider(),
                      ListTile(title: const Text('Email'), subtitle: Text(_teacher!['email'] ?? 'N/A')),
                      ListTile(title: const Text('Phone'), subtitle: Text(_teacher!['phone'] ?? 'N/A')),
                      ListTile(title: const Text('Username'), subtitle: Text(_teacher!['username'] ?? 'N/A')),
                    ],
                  ),
                ),
    );
  }
}
