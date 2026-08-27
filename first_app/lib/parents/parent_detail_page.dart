import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as dev;
import 'package:first_app/config.dart';

class ParentDetailPage extends StatefulWidget {
  final int parentsID;
  final Map userData;

  const ParentDetailPage({super.key, required this.parentsID, required this.userData});

  @override
  State<ParentDetailPage> createState() => _ParentDetailPageState();
}

class _ParentDetailPageState extends State<ParentDetailPage> {
  Map? _parent;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/parent_view';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'parentsID': widget.parentsID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _parent = result['data'];
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
      appBar: AppBar(title: const Text('Parent Profile')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _parent == null
              ? const Center(child: Text('Failed to load profile'))
              : Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    children: [
                      const CircleAvatar(radius: 50, child: Icon(Icons.person, size: 50)),
                      const SizedBox(height: 16),
                      Text(_parent!['name'] ?? '', style: Theme.of(context).textTheme.headlineMedium),
                      const Divider(),
                      ListTile(title: const Text('Email'), subtitle: Text(_parent!['email'] ?? 'N/A')),
                      ListTile(title: const Text('Phone'), subtitle: Text(_parent!['phone'] ?? 'N/A')),
                      ListTile(title: const Text('Username'), subtitle: Text(_parent!['username'] ?? 'N/A')),
                    ],
                  ),
                ),
    );
  }
}
