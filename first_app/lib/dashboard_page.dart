import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'dart:convert';
import 'dart:developer' as dev;

import 'widgets/app_drawer.dart';
import 'student/student_list_page.dart';
import 'teacher/teacher_list_page.dart';
import 'classes/class_list_page.dart';
import 'section/section_list_page.dart';
import 'parents/parent_list_page.dart';
import 'user/user_list_page.dart';
import 'config.dart';

class DashboardPage extends StatefulWidget {
  final Map userData;

  const DashboardPage({super.key, required this.userData});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  Map<String, dynamic> _stats = {};
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchStats();
  }

  Future<void> _fetchStats() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/dashboard_data';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'schoolyearID': widget.userData['defaultschoolyearID'],
          'campusID': widget.userData['campusID'] ?? 0,
        }),
      );

      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _stats = result['data'];
          _isLoading = false;
        });
      }
    } catch (e) {
      dev.log('Stats Error: $e', name: 'UI');
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('${AppConfig.appSettings['sname']} Dashboard'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _fetchStats),
        ],
      ),
      drawer: AppDrawer(userData: widget.userData),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Welcome, ${widget.userData['name']}',
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 20),
                  GridView.count(
                    crossAxisCount: 2,
                    crossAxisSpacing: 16,
                    mainAxisSpacing: 16,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    children: [
                      _buildStatCard(
                        'Students',
                        _stats['students'],
                        Icons.school,
                        Colors.blue,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) =>
                                  StudentListPage(userData: widget.userData),
                            ),
                          );
                        },
                      ),
                      _buildStatCard(
                        'Teachers',
                        _stats['teachers'],
                        Icons.person,
                        Colors.green,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) =>
                                  TeacherListPage(userData: widget.userData),
                            ),
                          );
                        },
                      ),
                      _buildStatCard(
                        'Classes',
                        _stats['classes'],
                        Icons.room,
                        Colors.orange,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) =>
                                  ClassListPage(userData: widget.userData),
                            ),
                          );
                        },
                      ),
                      _buildStatCard(
                        'Parents',
                        _stats['parents'],
                        Icons.family_restroom,
                        Colors.purple,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) =>
                                  ParentListPage(userData: widget.userData),
                            ),
                          );
                        },
                      ),
                      _buildStatCard(
                        'Users',
                        _stats['users'],
                        Icons.people,
                        Colors.indigo,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) =>
                                  UserListPage(userData: widget.userData),
                            ),
                          );
                        },
                      ),
                      _buildStatCard(
                        'Sections',
                        _stats['sections'],
                        Icons.meeting_room,
                        Colors.cyan,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) =>
                                  SectionListPage(userData: widget.userData),
                            ),
                          );
                        },
                      ),
                      _buildStatCard(
                        'Subjects',
                        _stats['subjects'],
                        Icons.book,
                        Colors.red,
                      ),
                      _buildStatCard(
                        'Invoices',
                        _stats['invoices'],
                        Icons.attach_money,
                        Colors.teal,
                      ),
                    ],
                  ),
                ],
              ),
            ),
    );
  }

  Widget _buildStatCard(
    String title,
    dynamic value,
    IconData icon,
    Color color, {
    VoidCallback? onTap,
  }) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 40, color: color),
              const SizedBox(height: 10),
              Text(
                value?.toString() ?? '0',
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(title, style: const TextStyle(color: Colors.grey)),
            ],
          ),
        ),
      ),
    );
  }
}
