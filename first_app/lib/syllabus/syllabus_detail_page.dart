import 'dart:convert';
import 'dart:developer' as dev;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';

import 'package:first_app/config.dart';

class SyllabusDetailPage extends StatefulWidget {
  final int syllabusID;
  final Map userData;

  const SyllabusDetailPage({
    super.key,
    required this.syllabusID,
    required this.userData,
  });

  @override
  State<SyllabusDetailPage> createState() => _SyllabusDetailPageState();
}

class _SyllabusDetailPageState extends State<SyllabusDetailPage> {
  Map?  _syllabus;
  bool  _isLoading = true;

  static const Color _primary = Color(0xFF4A148C);
  static const Color _accent  = Color(0xFFAB47BC);

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
        Uri.parse('${base}api/syllabus_view'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'syllabusID': widget.syllabusID,
          'adminID':    widget.userData['adminID'] ?? 1,
        }),
      );
      final result = jsonDecode(res.body);
      if (!mounted) return;
      if (result['status'] == true) {
        setState(() { _syllabus = result['data']; _isLoading = false; });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      dev.log('Syllabus detail error: $e');
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
    final fileUrl = _syllabus?['file_url']?.toString();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Syllabus Details'),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [_primary, _accent]),
          ),
        ),
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _syllabus == null
              ? const Center(child: Text('Failed to load syllabus details.'))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    // Header card
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
                              child: const Icon(Icons.menu_book, size: 40, color: _primary),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              _syllabus!['title'] ?? 'Syllabus',
                              textAlign: TextAlign.center,
                              style: Theme.of(context).textTheme.headlineSmall
                                  ?.copyWith(fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              _syllabus!['class_name']?.toString() ?? '',
                              style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey),
                            ),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    // Detail tiles
                    Card(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      child: Column(
                        children: [
                          _tile(Icons.title,           'Title',       _syllabus!['title']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.class_,          'Class',       _syllabus!['class_name']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.location_city,   'Campus',      _syllabus!['campus_name']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.calendar_today,  'Date',        _syllabus!['date']?.toString()),
                          const Divider(height: 1),
                          _tile(Icons.description,     'Description', _syllabus!['description']?.toString()),
                        ],
                      ),
                    ),

                    // File download button
                    if (fileUrl != null && fileUrl.isNotEmpty) ...[
                      const SizedBox(height: 16),
                      ElevatedButton.icon(
                        onPressed: () async {
                          final uri = Uri.tryParse(fileUrl);
                          if (uri != null && await canLaunchUrl(uri)) {
                            await launchUrl(uri, mode: LaunchMode.externalApplication);
                          } else {
                            if (context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Cannot open file')),
                              );
                            }
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: _primary,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        icon: const Icon(Icons.download),
                        label: Text(
                          'Download: ${_syllabus!['originalfile'] ?? 'File'}',
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ],
                ),
    );
  }
}
