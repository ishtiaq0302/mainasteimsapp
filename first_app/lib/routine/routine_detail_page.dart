import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class RoutineDetailPage extends StatefulWidget {
  final int routineID;
  final Map userData;

  const RoutineDetailPage({
    super.key,
    required this.routineID,
    required this.userData,
  });

  @override
  State<RoutineDetailPage> createState() => _RoutineDetailPageState();
}

class _RoutineDetailPageState extends State<RoutineDetailPage> {
  Map?  _routine;
  bool  _isLoading = true;

  static const Color _primary = Color(0xFF1B5E20);
  static const Color _accent  = Color(0xFF66BB6A);

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    String base = AppConfig.baseUrl;
    if (!base.endsWith('/')) base += '/';
    try {
      final res = await http.post(
        Uri.parse('${base}api/routine_view'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'routineID': widget.routineID,
          'adminID':   widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(res.body);
      if (!mounted) return;
      if (result['status'] == true) {
        setState(() { _routine = result['data']; _isLoading = false; });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      dev.log('Routine detail error: $e');
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Widget _tile(IconData icon, String title, String? value) => ListTile(
        leading: Icon(icon, color: Colors.blueGrey),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(value?.isNotEmpty == true ? value! : '—'),
      );

  Color _dayColor(String? day) {
    switch (day) {
      case 'Saturday':  return Colors.purple.shade200;
      case 'Sunday':    return Colors.red.shade200;
      case 'Monday':    return Colors.blue.shade200;
      case 'Tuesday':   return Colors.teal.shade200;
      case 'Wednesday': return Colors.orange.shade200;
      case 'Thursday':  return Colors.green.shade200;
      case 'Friday':    return Colors.indigo.shade200;
      default:          return Colors.grey.shade300;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Routine Details'),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [_primary, _accent]),
          ),
        ),
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _routine == null
              ? const Center(child: Text('Failed to load routine details.'))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    // ── Header card ────────────────────────────────────────
                    Card(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      elevation: 3,
                      child: Padding(
                        padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 16),
                        child: Column(
                          children: [
                            CircleAvatar(
                              radius: 36,
                              backgroundColor: _primary.withOpacity(0.12),
                              child: const Icon(Icons.schedule, size: 40, color: _primary),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              _routine!['subject_name'] ?? 'Routine',
                              style: Theme.of(context).textTheme.headlineSmall
                                  ?.copyWith(fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                              decoration: BoxDecoration(
                                color: _dayColor(_routine!['day']?.toString()),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                _routine!['day'] ?? '',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${_routine!['start_time'] ?? ''} – ${_routine!['end_time'] ?? ''}',
                              style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey),
                            ),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    // ── Detail tiles ───────────────────────────────────────
                    Card(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      child: Column(
                        children: [
                          _tile(Icons.book,             'Subject',     _routine!['subject_name']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.class_,           'Class',       _routine!['class_name']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.meeting_room,     'Section',     _routine!['section_name']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.person,           'Teacher',     _routine!['teacher_name']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.calendar_today,   'Day',         _routine!['day']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.access_time,      'Start Time',  _routine!['start_time']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.access_time_filled,'End Time',   _routine!['end_time']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.door_front_door,  'Room',        _routine!['room']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.school,           'School Year', _routine!['school_year']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.location_city,    'Campus ID',   _routine!['campusID']?.toString()),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }
}
