import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as dev;
import 'package:first_app/config.dart';

class ParentFormPage extends StatefulWidget {
  final Map? parentData;
  final Map userData;
  const ParentFormPage({super.key, this.parentData, required this.userData});

  @override
  State<ParentFormPage> createState() => _ParentFormPageState();
}

class _ParentFormPageState extends State<ParentFormPage> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();

  List _campuses = [];
  String? _selectedCampus;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _fetchCampuses();
    if(widget.parentData != null) {
      _nameController.text = widget.parentData!['name'] ?? '';
      _emailController.text = widget.parentData!['email'] ?? '';
      _phoneController.text = widget.parentData!['phone'] ?? '';
      _usernameController.text = widget.parentData!['username'] ?? '';
      _selectedCampus = widget.parentData!['campusID'].toString();
    }
  }

  Future<void> _fetchCampuses() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/metadata';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': 0}),
      );
      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _campuses = result['data']['campuses'];
        });
      }
    } catch (e) {
      dev.log('Campuses Error: $e');
    }
  }

  Future<void> _save() async {
    if(!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = widget.parentData == null ? '${base}api/parent_add' : '${base}api/parent_update';
    
    Map<String, dynamic> body = {
      'name': _nameController.text,
      'email': _emailController.text,
      'phone': _phoneController.text,
      'campusID': _selectedCampus,
      'adminID': widget.userData['adminID'] ?? 1,
    };

    if(widget.parentData == null) {
      body['username'] = _usernameController.text;
      body['password'] = _passwordController.text;
    } else {
      body['parentsID'] = widget.parentData!['parentsID'];
    }

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );
      final result = jsonDecode(response.body);
      if(result['status'] == true) {
        if(!mounted) return;
        Navigator.pop(context, true);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result['message'])));
      } else {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result['message'])));
      }
    } catch (e) {
      dev.log('Save Parent Error: $e');
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.parentData == null ? 'Add Parent' : 'Edit Parent')),
      body: _isLoading 
        ? const Center(child: CircularProgressIndicator())
        : Padding(
            padding: const EdgeInsets.all(16.0),
            child: Form(
              key: _formKey,
              child: ListView(
                children: [
                  DropdownButtonFormField<String>(
                    decoration: const InputDecoration(labelText: 'Campus'),
                    value: _selectedCampus,
                    items: _campuses.map((c) => DropdownMenuItem<String>(value: c['campusID'].toString(), child: Text(c['name']))).toList(),
                    onChanged: (v) => setState(() => _selectedCampus = v),
                    validator: (v) => v == null ? 'Required' : null,
                  ),
                  TextFormField(controller: _nameController, decoration: const InputDecoration(labelText: 'Full Name'), validator: (v) => v!.isEmpty ? 'Required' : null),
                  TextFormField(controller: _emailController, decoration: const InputDecoration(labelText: 'Email')),
                  TextFormField(controller: _phoneController, decoration: const InputDecoration(labelText: 'Phone')),
                  if(widget.parentData == null) ...[
                    TextFormField(controller: _usernameController, decoration: const InputDecoration(labelText: 'Username'), validator: (v) => v!.isEmpty ? 'Required' : null),
                    TextFormField(controller: _passwordController, decoration: const InputDecoration(labelText: 'Password'), obscureText: true, validator: (v) => v!.isEmpty ? 'Required' : null),
                  ],
                  const SizedBox(height: 20),
                  ElevatedButton(onPressed: _save, child: const Text('Save')),
                ],
              ),
            ),
          ),
    );
  }
}
