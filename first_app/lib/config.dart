// Global Configuration and Settings
class AppConfig {
  // Use the machine IP on the local network so the Android phone can reach the XAMPP server.
  // For this PC, the active Wi-Fi adapter is 192.168.1.6.
  static const String baseUrl =
      'http://192.168.1.6/ASTProjects/mainasteimsapp/';

  static Map<String, dynamic> appSettings = {
    'sname': 'ASTEIMS', // Default fallback
    'footer': 'Azam Systems & Technologies',
  };
}
