import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as dev;
import 'package:first_app/config.dart';

class UserFormPage extends StatefulWidget {
  final Map? userDataForm;
  final Map userData;
  const UserFormPage({super.key, this.userDataForm, required this.userData});

  @override
  State<UserFormPage> createState() => _UserFormPageState();
}

class _UserFormPageState extends State<UserFormPage> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();

  List _campuses = [];
  List _usertypes = [];
  String? _selectedCampus;
  String? _selectedUsertype;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _fetchMetadata();
    if(widget.userDataForm != null) {
      _nameController.text = widget.userDataForm!['name'] ?? '';
      _emailController.text = widget.userDataForm!['email'] ?? '';
      _phoneController.text = widget.userDataForm!['phone'] ?? '';
      _usernameController.text = widget.userDataForm!['username'] ?? '';
      _selectedCampus = widget.userDataForm!['campusID'].toString();
      _selectedUsertype = widget.userDataForm!['usertypeID'].toString();
    }
  }

  Future<void> _fetchMetadata() async {
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
          // For usertypes, we'd normally fetch them from metadata too. 
          // Since I haven't updated metadata API yet for usertypes, I'll add a separate fetch if needed 
          // or assume common ones for now. Let's assume metadata fetch is enough for campus.
        });
      }
    } catch (e) {
      dev.log('Metadata Error: $e');
    }
  }

  Future<void> _save() async {
    if(!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = widget.userDataForm == null ? '${base}api/user_add' : '${base}api/user_update';
    
    Map<String, dynamic> body = {
      'name': _nameController.text,
      'email': _emailController.text,
      'phone': _phoneController.text,
      'campusID': _selectedCampus,
      'usertypeID': _selectedUsertype,
      'adminID': widget.userData['adminID'] ?? 1,
    };

    if(widget.userDataForm == null) {
      body['username'] = _usernameController.text;
      body['password'] = _passwordController.text;
    } else {
      body['userID'] = widget.userDataForm!['userID'];
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
      dev.log('Save User Error: $e');
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.userDataForm == null ? 'Add User' : 'Edit User')),
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
                  DropdownButtonFormField<String>(
                    decoration: const InputDecoration(labelText: 'User Role'),
                    value: _selectedUsertype,
                    items: [
                      const DropdownMenuItem(value: '1', child: Text('Admin')),
                      const DropdownMenuItem(value: '5', child: Text('Librarian')),
                      const DropdownMenuItem(value: '6', child: Text('Accountant')),
                    ],
                    onChanged: (v) => setState(() => _selectedUsertype = v),
                    validator: (v) => v == null ? 'Required' : null,
                  ),
                  TextFormField(controller: _nameController, decoration: const InputDecoration(labelText: 'Full Name'), validator: (v) => v!.isEmpty ? 'Required' : null),
                  TextFormField(controller: _emailController, decoration: const InputDecoration(labelText: 'Email')),
                  TextFormField(controller: _phoneController, decoration: const InputDecoration(labelText: 'Phone')),
                  if(widget.userDataForm == null) ...[
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
