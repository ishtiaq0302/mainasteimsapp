import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as dev;
import 'dashboard_page.dart';
import 'package:first_app/config.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  
  bool _isLoading = false;
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
    _fetchSettings();
  }

  Future<void> _fetchSettings() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/settings';

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'campusID': 1}), // Initial fetch with default campus
      );

      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          AppConfig.appSettings = result['data'];
        });
      }
    } catch (e) {
      dev.log('Settings Fetch Error: $e');
    }
  }

  void _resetState() {
    setState(() {
      _isLoading = false;
      _errorMessage = '';
    });
  }

  Future<void> _login() async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    // Use baseUrl from main.dart
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    
    final String apiUrl = '${base}api/login';

    dev.log('Attempting login to: $apiUrl', name: 'Auth');

    try {
      final response = await http
          .post(
            Uri.parse(apiUrl),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({
              'username': _usernameController.text,
              'password': _passwordController.text,
            }),
          )
          .timeout(const Duration(seconds: 20));

      dev.log('Response Status: ${response.statusCode}', name: 'Auth');

      final result = jsonDecode(response.body);

      if (response.statusCode == 200 && result['status'] == true) {
        if (!mounted) return;
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => DashboardPage(userData: result['data']),
          ),
        );
      } else {
        setState(() {
          _errorMessage = result['message'] ?? 'Login failed. Please check your credentials.';
        });
      }
    } catch (e) {
      dev.log('Login Error: $e', name: 'Auth', error: e);
      String userFriendlyError = 'Connection Error: $e';

      if (e.toString().contains('TimeoutException')) {
        userFriendlyError = 'Connection Timeout.\nCheck Server status.';
      }

      setState(() {
        _errorMessage = userFriendlyError;
      });
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('${AppConfig.appSettings['sname']} Login'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _resetState,
          ),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Center(
          child: SingleChildScrollView(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.school, size: 80, color: Colors.blue),
                const SizedBox(height: 30),
                TextField(
                  controller: _usernameController,
                  decoration: const InputDecoration(
                    labelText: 'Username',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.person),
                  ),
                ),
                const SizedBox(height: 20),
                TextField(
                  controller: _passwordController,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: 'Password',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.lock),
                  ),
                ),
                if (_errorMessage.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 20),
                    child: Text(_errorMessage, style: const TextStyle(color: Colors.red)),
                  ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _login,
                    child: _isLoading ? const CircularProgressIndicator() : const Text('Login'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
