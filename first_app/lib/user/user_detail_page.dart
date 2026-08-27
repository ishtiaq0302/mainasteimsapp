import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as dev;
import 'package:first_app/config.dart';

class UserDetailPage extends StatefulWidget {
  final int userID;
  final Map userData;

  const UserDetailPage({super.key, required this.userID, required this.userData});

  @override
  State<UserDetailPage> createState() => _UserDetailPageState();
}

class _UserDetailPageState extends State<UserDetailPage> {
  Map? _user;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/user_view';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'userID': widget.userID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _user = result['data'];
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
      appBar: AppBar(title: const Text('User Profile')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _user == null
              ? const Center(child: Text('Failed to load profile'))
              : Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    children: [
                      const CircleAvatar(radius: 50, child: Icon(Icons.person, size: 50)),
                      const SizedBox(height: 16),
                      Text(_user!['name'] ?? '', style: Theme.of(context).textTheme.headlineMedium),
                      Text(_user!['usertype'] ?? 'User', style: const TextStyle(fontSize: 18, color: Colors.grey)),
                      const Divider(),
                      ListTile(title: const Text('Email'), subtitle: Text(_user!['email'] ?? 'N/A')),
                      ListTile(title: const Text('Phone'), subtitle: Text(_user!['phone'] ?? 'N/A')),
                      ListTile(title: const Text('Username'), subtitle: Text(_user!['username'] ?? 'N/A')),
                    ],
                  ),
                ),
    );
  }
}
