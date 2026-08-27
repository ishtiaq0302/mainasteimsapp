import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class SectionDetailPage extends StatefulWidget {
  final int sectionID;
  final Map userData;

  const SectionDetailPage({
    super.key,
    required this.sectionID,
    required this.userData,
  });

  @override
  State<SectionDetailPage> createState() => _SectionDetailPageState();
}

class _SectionDetailPageState extends State<SectionDetailPage> {
  Map? _section;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final response = await http.post(
        Uri.parse('${base}api/section_view'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'sectionID': widget.sectionID,
          'adminID': widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      if (result['status'] == true) {
        setState(() { _section = result['data']; _isLoading = false; });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      dev.log('Section detail error: $e');
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Widget _tile(IconData icon, String title, String? value) => ListTile(
        leading: Icon(icon, color: Colors.blueGrey),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(value?.isNotEmpty == true ? value! : '—'),
      );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Section Details')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _section == null
              ? const Center(child: Text('Failed to load section details'))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    const Icon(Icons.meeting_room, size: 64, color: Colors.blue),
                    const SizedBox(height: 12),
                    Center(
                      child: Text(
                        _section!['section'] ?? 'Section',
                        style: Theme.of(context).textTheme.headlineSmall,
                      ),
                    ),
                    Center(
                      child: Text(
                        _section!['class_name'] ?? '',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey),
                      ),
                    ),
                    const Divider(height: 28),
                    _tile(Icons.category,      'Category',  _section!['category']?.toString()),
                    _tile(Icons.people,        'Capacity',  _section!['capacity']?.toString()),
                    _tile(Icons.person,        'Teacher',   _section!['teacher_name']?.toString()),
                    _tile(Icons.school,        'Class',     _section!['class_name']?.toString()),
                    _tile(Icons.location_city, 'Campus ID', _section!['campusID']?.toString()),
                    _tile(Icons.note,          'Note',      _section!['note']?.toString()),
                    _tile(Icons.calendar_today,'Created',   _section!['create_date']?.toString()),
                    _tile(Icons.edit_calendar, 'Modified',  _section!['modify_date']?.toString()),
                  ],
                ),
    );
  }
}
