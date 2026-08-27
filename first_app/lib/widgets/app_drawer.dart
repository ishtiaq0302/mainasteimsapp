import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'dart:convert';
import 'dart:developer' as dev;

import '../student/student_list_page.dart';
import '../classes/class_list_page.dart';
import '../section/section_list_page.dart';
import '../parents/parent_list_page.dart';
import '../teacher/teacher_list_page.dart';
import '../user/user_list_page.dart';
import '../login_page.dart';

import 'package:first_app/config.dart';

class AppDrawer extends StatefulWidget {
  final Map userData;

  const AppDrawer({super.key, required this.userData});

  @override
  State<AppDrawer> createState() => _AppDrawerState();
}

class _AppDrawerState extends State<AppDrawer> {
  List _menuItems = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchMenu();
  }

  Future<void> _fetchMenu() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    final String apiUrl = '${base}api/sidebar_menu';
    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'usertypeID': widget.userData['usertypeID']}),
      );

      final result = jsonDecode(response.body);
      if (result['status'] == true) {
        setState(() {
          _menuItems = result['data'];
          _isLoading = false;
        });
      }
    } catch (e) {
      dev.log('Menu Error: $e', name: 'UI');
      setState(() => _isLoading = false);
    }
  }

  IconData _getIcon(String? iconName) {
    if (iconName == null) return Icons.circle;
    iconName = iconName.toLowerCase();
    if (iconName.contains('dashboard')) return Icons.dashboard;
    if (iconName.contains('student'))   return Icons.school;
    if (iconName.contains('class'))     return Icons.class_;
    if (iconName.contains('section'))   return Icons.meeting_room;
    if (iconName.contains('user') || iconName.contains('people')) return Icons.people;
    if (iconName.contains('teacher'))   return Icons.person;
    if (iconName.contains('setting'))   return Icons.settings;
    return Icons.arrow_right;
  }

  void _navigate(String link) {
    if (link == 'student') {
      Navigator.pop(context);
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => StudentListPage(userData: widget.userData),
        ),
      );
    } else if (link == 'parents') {
      Navigator.pop(context);
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => ParentListPage(userData: widget.userData),
        ),
      );
    } else if (link == 'teacher') {
      Navigator.pop(context);
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => TeacherListPage(userData: widget.userData),
        ),
      );
    } else if (link == 'class' || link == 'classes') {
      Navigator.pop(context);
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => ClassListPage(userData: widget.userData),
        ),
      );
    } else if (link == 'section' || link == 'sections') {
      Navigator.pop(context);
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => SectionListPage(userData: widget.userData),
        ),
      );
    } else if (link == 'user') {
      Navigator.pop(context);
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => UserListPage(userData: widget.userData),
        ),
      );
    } else {
      dev.log('Navigation to $link not implemented', name: 'UI');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: Column(
        children: [
          UserAccountsDrawerHeader(
            decoration: const BoxDecoration(color: Colors.blue),
            accountName: Text(widget.userData['name'] ?? 'User'),
            accountEmail: Text(AppConfig.appSettings['sname']),
            currentAccountPicture: const CircleAvatar(
              backgroundColor: Colors.white,
              child: Icon(Icons.person, size: 40, color: Colors.blue),
            ),
          ),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : ListView.builder(
                    itemCount: _menuItems.length,
                    itemBuilder: (context, index) {
                      final item = _menuItems[index];
                      final children = item['child'] as List?;

                      if (children != null && children.isNotEmpty) {
                        return ExpansionTile(
                          leading: Icon(_getIcon(item['icon'])),
                          title: Text(item['menuName']),
                          children: children.map<Widget>((child) {
                            return ListTile(
                              contentPadding: const EdgeInsets.only(left: 40),
                              title: Text(child['menuName']),
                              onTap: () => _navigate(child['link']),
                            );
                          }).toList(),
                        );
                      } else {
                        return ListTile(
                          leading: Icon(_getIcon(item['icon'])),
                          title: Text(item['menuName']),
                          onTap: () => _navigate(item['link']),
                        );
                      }
                    },
                  ),
          ),
          const Divider(),
          ListTile(
            leading: const Icon(Icons.logout),
            title: const Text('Logout'),
            onTap: () => Navigator.of(context).pushReplacement(
              MaterialPageRoute(builder: (context) => LoginPage()),
            ),
          ),
        ],
      ),
    );
  }
}
