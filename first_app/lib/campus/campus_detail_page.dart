import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'package:first_app/config.dart';

class CampusDetailPage extends StatefulWidget {
  final int campusID;
  final Map userData;

  const CampusDetailPage({
    super.key,
    required this.campusID,
    required this.userData,
  });

  @override
  State<CampusDetailPage> createState() => _CampusDetailPageState();
}

class _CampusDetailPageState extends State<CampusDetailPage> {
  Map?  _campus;
  bool  _isLoading = true;

  static const Color _primary = Color(0xFF1565C0);

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
        Uri.parse('${base}api/campus_view'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campusID': widget.campusID,
          'adminID':  widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(response.body);
      if (!mounted) return;
      if (result['status'] == true) {
        setState(() { _campus = result['data']; _isLoading = false; });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      dev.log('Campus detail error: $e');
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
      appBar: AppBar(
        title: const Text('Campus Details'),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [_primary, Color(0xFF42A5F5)]),
          ),
        ),
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _campus == null
              ? const Center(child: Text('Failed to load campus details.'))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    // ── Header card ───────────────────────────────────────
                    Card(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      elevation: 3,
                      child: Padding(
                        padding: const EdgeInsets.symmetric(vertical: 28),
                        child: Column(
                          children: [
                            CircleAvatar(
                              radius: 36,
                              backgroundColor: _primary.withOpacity(0.12),
                              child: const Icon(Icons.location_city, size: 40, color: _primary),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              _campus!['name'] ?? 'Campus',
                              style: Theme.of(context).textTheme.headlineSmall
                                  ?.copyWith(fontWeight: FontWeight.bold),
                            ),
                            Text(
                              'Campus ID: ${_campus!['campusID'] ?? ''}',
                              style: Theme.of(context)
                                  .textTheme
                                  .bodyMedium
                                  ?.copyWith(color: Colors.grey),
                            ),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    // ── Detail tiles ──────────────────────────────────────
                    Card(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      child: Column(
                        children: [
                          _tile(Icons.location_city, 'Campus Name', _campus!['name']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.badge,          'Campus ID',   _campus!['campusID']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.admin_panel_settings, 'Admin ID', _campus!['adminID']?.toString()),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }
}
