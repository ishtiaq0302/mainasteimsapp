# Walkthrough - Advanced Student Table Enhancements

I have significantly upgraded the Student Management system with a feature-rich, responsive data table and real-time status controls.

## Changes Made

### Backend APIs
- **[student_status.php](file:///E:/xampp/htdocs/ASTProjects/mainasteimsapp/api/student_status.php)**: [NEW] A dedicated API to handle instant status (Active/Inactive) toggles from the Flutter app.
- **[db.php](file:///E:/xampp/htdocs/ASTProjects/mainasteimsapp/api/db.php)**: Centralized database and header configuration used by all APIs.

### Flutter UI (Student List Page)
- **Responsive Data Table**:
    - Implemented a **full-width, scrollable table** that works perfectly on both small phones and tablets.
    - Uses `PaginatedDataTable` for high performance with large datasets (10 records per page).
- **Search & Filter**:
    - **Live Search**: Added a search bar at the top to filter students by **Name, Roll, or Email** instantly.
    - **Cascading Dropdowns**: Campus and Class selection with automated dependency loading.
    - **Dynamic Tabs**: Auto-generates section tabs (e.g., A, B) once a class is selected.
- **Rich Media & Controls**:
    - **Photos**: Displays actual student photos from the server with a fallback initial avatar.
    - **Real-time Toggle**: Replaced static status labels with a **Switch**. Toggling it updates the database immediately.
    - **Modern Actions**: Clean "Three Dots" menu for **Detail, Edit, and Delete** text actions.
- **Data Export**:
    - Added a **Download Toolbar** (PDF, Excel, CSV).
    - **PDF**: Generates a high-quality document and opens the system print/preview dialog.
    - **Excel/CSV**: Saves formatted data files directly to the device's document storage.

### Configuration
- **Default IP**: Set to `localhost` in the Login Page for easier local development testing.

## How to Test

1.  **Open Sidebar**: Go to **Users > Student**.
2.  **Filter**: Pick a Campus and a Class to load the data.
3.  **Search**: Start typing a student's name in the search bar to see real-time filtering.
4.  **Toggle Status**: Flip the green switch on any row and verify the "Status updated" message.
5.  **Export**:
    - Tap the **Download icon** in the top bar.
    - Select **Export PDF** to see a generated report.
    - Select **Export Excel/CSV** to save the records to your device.
6.  **Responsive View**: Resize your window or use a small phone emulator to see the horizontal table scrolling in action.

> [!TIP]
> The "All Students" tab shows the entire class, while the section tabs help you drill down into specific groups.

> [!IMPORTANT]
> Ensure your XAMPP server is running so the app can reach the APIs in the `api/` folder.
