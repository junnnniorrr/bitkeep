<?php
// Initialize session
session_start();

// Include database connection
require_once("include/connection.php");

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.html');
    exit();
}

// Check if file_id is provided
if (!isset($_GET['file_id'])) {
    echo '<script>alert("File ID is required!"); window.history.back();</script>';
    exit();
}

$file_id = $_GET['file_id'];

// Get file details from database - Check which columns actually exist in your database
$query = "SELECT ff.*, f.FOLDER_NAME, f.folder_id 
          FROM folder_files ff 
          JOIN folders f ON ff.folder_id = f.folder_id 
          WHERE ff.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<script>alert("File not found in database!"); window.history.back();</script>';
    exit();
}

$file = $result->fetch_assoc();
$folder_id = $file['folder_id'];

// Debug - Log file details to a file for troubleshooting
error_log("File ID: " . $file_id . ", File details: " . print_r($file, true));

// Determine the correct file name field and construct the path
$file_name_field = '';
if (isset($file['filename']) && !empty($file['filename'])) {
    $file_name_field = 'filename';
} elseif (isset($file['file_name']) && !empty($file['file_name'])) {
    $file_name_field = 'file_name';
} elseif (isset($file['name']) && !empty($file['name'])) {
    $file_name_field = 'name';
} else {
    // If neither exists, use ID as fallback
    $file_name_field = 'id';
}

// Check for file path in database if it exists
$file_path = '';
if (isset($file['file_path']) && !empty($file['file_path'])) {
    $file_path = $file['file_path'];
} else {
    // Construct file path using available fields
    $file_path = "uploads/folder_files/" . $file[$file_name_field];
}

// Verify absolute path for debugging
$absolute_path = realpath($file_path);
error_log("Looking for file at: " . $file_path . ", Absolute path: " . $absolute_path);

// Check if file exists before proceeding
if (!file_exists($file_path)) {
    // Try alternative paths
    $alternative_paths = [
        "uploads/" . $file[$file_name_field],
        "uploads/files/" . $file[$file_name_field],
        "../uploads/folder_files/" . $file[$file_name_field],
        "folder_files/" . $file[$file_name_field],
        $file[$file_name_field] // Direct path
    ];
    
    $found = false;
    foreach ($alternative_paths as $alt_path) {
        error_log("Trying alternative path: " . $alt_path);
        if (file_exists($alt_path)) {
            $file_path = $alt_path;
            $found = true;
            error_log("File found at: " . $alt_path);
            break;
        }
    }
    
    if (!$found) {
        error_log("File not found at any location: original path = " . $file_path);
        // Continue execution - will show "File Not Found" in the preview area
    }
}

$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// FIX 2: Check if 'name' column exists, if not use an alternative
$file_name = isset($file['name']) ? $file['name'] : 
             (isset($file['file_name']) ? $file['file_name'] : 
             (isset($file['filename']) ? $file['filename'] : "File #".$file['id']));

// Log the file view - FIX 3: Check if admin_logs table exists before inserting
$admin_user = $_SESSION['admin_user'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $admin_user;
$action = "Previewed file: " . $file_name . " from folder: " . $file['FOLDER_NAME'];

// First check if the admin_logs table exists
$table_check = $conn->query("SHOW TABLES LIKE 'admin_logs'");
if($table_check->num_rows > 0) {
    // Table exists, proceed with logging
    $log_query = "INSERT INTO admin_logs(admin_user, action, created_at) VALUES(?, ?, NOW())";
    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bind_param("ss", $admin_user, $action);
    $log_stmt->execute();
} else {
    // Table doesn't exist, create it first
    $create_table_query = "CREATE TABLE admin_logs (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        admin_user VARCHAR(100) NOT NULL,
        action TEXT NOT NULL,
        created_at DATETIME NOT NULL
    )";
    
    if($conn->query($create_table_query)) {
        // Now insert the log
        $log_query = "INSERT INTO admin_logs(admin_user, action, created_at) VALUES(?, ?, NOW())";
        $log_stmt = $conn->prepare($log_query);
        $log_stmt->bind_param("ss", $admin_user, $action);
        $log_stmt->execute();
    }
}

// Get admin info for display
$id = mysqli_real_escape_string($conn, $_SESSION['admin_user']);
$r = mysqli_query($conn, "SELECT * FROM admin_login where id = '$id'") or die(mysqli_error($conn));
$row = mysqli_fetch_array($r);
$admin_email = $row['admin_user'];
$admin_avatar = strtoupper(substr($admin_email, 0, 1));

// Function to get viewer type for various file formats
function getViewerType($extension) {
    $viewers = [
        'pdf' => 'pdf',
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image',
        'txt' => 'text',
        'csv' => 'text',
        'html' => 'text',
        'htm' => 'text',
        'doc' => 'office',
        'docx' => 'office',
        'xls' => 'office',
        'xlsx' => 'office',
        'ppt' => 'office',
        'pptx' => 'office'
    ];
    
    return isset($viewers[$extension]) ? $viewers[$extension] : 'unknown';
}

// Get viewer type for this file
$viewer_type = getViewerType($file_extension);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File Preview - <?php echo htmlspecialchars($file_name); ?></title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- PDF.js for PDF viewing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>
        // Set the PDF.js workerSrc
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    </script>
    
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: rgba(67, 97, 238, 0.1);
            --primary-dark: #3a56d4;
            --secondary-color: #f72585;
            --secondary-light: rgba(247, 37, 133, 0.1);
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --border-radius-sm: 0.375rem;
            --border-radius: 0.5rem;
            --border-radius-lg: 0.75rem;
            --box-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --box-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --box-shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.2s ease-in-out;
            --sidebar-width: 280px;
            --navbar-height: 70px;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Inter', sans-serif;
            color: var(--dark-color);
            overflow-x: hidden;
            padding-top: var(--navbar-height);
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: white;
            box-shadow: var(--box-shadow);
            z-index: 1000;
            transition: all 0.3s ease;
            padding-top: 20px;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 15px;
        }

        .sidebar-header img {
            max-width: 180px;
            transition: transform 0.3s ease;
        }
        
        .sidebar-header img:hover {
            transform: scale(1.05);
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--dark-color);
            transition: all 0.3s;
            border-radius: 8px;
            margin: 5px 15px;
            font-weight: 500;
            text-decoration: none;
        }

        .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-link:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
            transform: translateX(3px);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.2);
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
        }

        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 20px;
            box-shadow: var(--box-shadow);
            margin-bottom: 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 999;
            height: var(--navbar-height);
            transition: all 0.3s ease;
        }

        .user-welcome {
            font-weight: 600;
            color: var(--dark-color);
            display: flex;
            align-items: center;
        }
        
        .user-welcome .user-avatar {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .sign-out-btn {
            color: var(--danger-color);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s;
            border: 1px solid var(--border-color);
            background-color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .sign-out-btn:hover {
            background-color: rgba(239, 35, 60, 0.1);
            color: var(--danger-color);
            transform: translateY(-2px);
        }

        /* Breadcrumb */
        .breadcrumb-container {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            margin-bottom: 25px;
        }

        .breadcrumb {
            margin-bottom: 0;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.2s;
        }

        .breadcrumb-item a:hover {
            color: var(--secondary-color);
        }

        .breadcrumb-item.active {
            color: var(--dark-color);
            font-weight: 600;
        }

        /* Preview Header */
        .preview-header {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .file-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-light);
            color: var(--primary-color);
            font-size: 1.75rem;
        }
        
        .file-icon-wrapper.pdf {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .file-icon-wrapper.doc {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        
        .file-icon-wrapper.xls {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .file-icon-wrapper.ppt {
            background-color: rgba(249, 115, 22, 0.1);
            color: #f97316;
        }
        
        .file-icon-wrapper.img {
            background-color: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }
        
        .file-icon-wrapper.txt {
            background-color: rgba(100, 116, 139, 0.1);
            color: var(--gray-500);
        }
        
        .file-details {
            flex: 1;
            min-width: 0; /* Ensures text truncation works */
        }
        
        .file-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        
        .file-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--gray-500);
            font-size: 0.875rem;
            flex-wrap: wrap;
        }
        
        .file-meta-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .file-type-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .file-type-badge.pdf {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .file-type-badge.doc {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        
        .file-type-badge.xls {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .file-type-badge.ppt {
            background-color: rgba(249, 115, 22, 0.1);
            color: #f97316;
        }
        
        .file-type-badge.img {
            background-color: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }
        
        .file-type-badge.txt {
            background-color: rgba(100, 116, 139, 0.1);
            color: var(--gray-500);
        }
        
        .action-buttons {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--box-shadow);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #e91e78;
            transform: translateY(-1px);
            box-shadow: var(--box-shadow);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }
        
        .btn-outline:hover {
            background-color: var(--gray-100);
            border-color: var(--gray-400);
        }
        
        .btn-icon {
            width: 2.5rem;
            height: 2.5rem;
            padding: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .preview-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
            margin-bottom: 2rem;
        }
        
        .preview-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.5rem;
            background-color: var(--gray-100);
            border-bottom: 1px solid var(--gray-200);
        }
        
        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .toolbar-title {
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }
        
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .toolbar-btn {
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius-sm);
            background-color: white;
            color: var(--gray-600);
            border: 1px solid var(--gray-200);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .toolbar-btn:hover {
            background-color: var(--gray-100);
            color: var(--gray-800);
        }
        
        .toolbar-btn.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .preview-content {
            min-height: 600px;
            max-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .preview-image-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--gray-100);
            position: relative;
        }
        
        .image-controls {
            position: absolute;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            box-shadow: var(--box-shadow);
        }
        
        .image-control-btn {
            width: 2.5rem;
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: white;
            color: var(--gray-700);
            border: 1px solid var(--gray-200);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .image-control-btn:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }
        
        .zoom-level {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            min-width: 3rem;
            text-align: center;
        }
        
        #pdf-viewer {
            width: 100%;
            height: 80vh;
            border: none;
        }
        
        .preview-text {
            width: 100%;
            height: 80vh;
            padding: 1.5rem;
            font-family: 'Roboto Mono', monospace;
            font-size: 0.875rem;
            line-height: 1.7;
            white-space: pre-wrap;
            overflow-y: auto;
            background-color: var(--gray-100);
            color: var(--gray-800);
            border: none;
            resize: none;
        }
        
        .office-viewer {
            width: 100%;
            height: 80vh;
            border: none;
        }
        
        .unsupported-file {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .unsupported-icon {
            font-size: 4rem;
            color: var(--gray-400);
            margin-bottom: 1.5rem;
        }
        
        .unsupported-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.75rem;
        }
        
        .unsupported-text {
            color: var(--gray-600);
            max-width: 500px;
            margin: 0 auto 2rem;
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        
        .loading-spinner {
            width: 3rem;
            height: 3rem;
            border: 3px solid var(--primary-light);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spinner 1s linear infinite;
            margin-bottom: 1rem;
        }
        
        .loading-text {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-600);
        }
        
        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }

        /* PDF.js custom viewer */
        .pdf-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--gray-100);
            border-bottom: 1px solid var(--gray-200);
        }
        
        .pdf-page-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .pdf-page-info {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
        }
        
        .pdf-container {
            width: 100%;
            height: calc(80vh - 3.5rem);
            overflow-y: auto;
            background-color: var(--gray-200);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            gap: 2rem;
        }
        
        .pdf-page {
            background-color: white;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius-sm);
        }

        /* Footer */
        .footer {
            background: white;
            padding: 20px;
            text-align: center;
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            margin-top: 30px;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
        }

        .footer p {
            margin: 0;
            color: var(--gray-color);
            font-size: 0.9rem;
        }

        /* Loader */
        #loader {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: rgba(255, 255, 255, 0.97);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loader-content {
            text-align: center;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(67, 97, 238, 0.1);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Mobile Sidebar Toggle */
        .sidebar-toggle {
            display: none;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            align-items: center;
            justify-content: center;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content, .top-navbar, .footer {
                margin-left: 0;
                width: 100%;
            }
            
            .top-navbar {
                left: 0;
            }
            
            .sidebar-toggle {
                display: flex;
            }
            
            .preview-content {
                min-height: 400px;
                max-height: 70vh;
            }
            
            #pdf-viewer, .office-viewer, .preview-text {
                height: 70vh;
            }
            
            .pdf-container {
                height: calc(70vh - 3.5rem);
                padding: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .top-navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px 15px;
                height: auto;
            }
            
            .sign-out-btn {
                margin-top: 10px;
            }
            
            .preview-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: space-between;
                margin-top: 1rem;
            }
            
            .file-meta {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .toolbar-title {
                display: none;
            }
        }
        
        @media (max-width: 576px) {
            .file-icon-wrapper {
                width: 48px;
                height: 48px;
                font-size: 1.5rem;
            }
            
            .file-name {
                font-size: 1.125rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
            }
            
            .image-controls {
                bottom: 1rem;
                padding: 0.375rem 0.75rem;
            }
            
            .image-control-btn {
                width: 2rem;
                height: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Loader -->
    <div id="loader">
        <div class="loader-content">
            <div class="spinner"></div>
            <h5 class="mt-3 text-muted">Loading...</h5>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="img/image1.svg" alt="BitKeep Logo">
        </div>
        <div class="list-group list-group-flush">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalRegisterForm">
                <i class="fas fa-user-plus"></i> Add Admin
            </a>
            <a href="view_admin.php" class="nav-link">
                <i class="fas fa-users"></i> View Admin
            </a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalRegisterForm2">
                <i class="fas fa-user-plus"></i> Add User
            </a>
            <a href="view_user.php" class="nav-link">
                <i class="fas fa-users"></i> View User
            </a>
            <a href="folder_management.php" class="nav-link active">
                <i class="fas fa-folder"></i> Folders
            </a>
            <a href="manage_requests.php" class="nav-link">
                <i class="fas fa-key"></i> Requests
            </a>
            <a href="add_document.php" class="nav-link">
                <i class="fas fa-file-medical"></i> Files
            </a>
            <a href="view_userfile.php" class="nav-link">
                <i class="fas fa-folder-open"></i> View User File
            </a>
            <a href="admin_log.php" class="nav-link">
                <i class="fas fa-history"></i> Admin Log
            </a>
            <a href="user_log.php" class="nav-link">
                <i class="fas fa-history"></i> User Log
            </a>
            </a>
            <a href="file_log.php" class="nav-link">
                <i class="fas fa-file-alt"></i> File Log
            </a>
             </a>
            <a href="security_logs.php" class="nav-link">
                <i class="fas fa-lock"></i> Security Log
            </a>
        </div>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="user-welcome">
            <div class="user-avatar">
                <?php echo $admin_avatar; ?>
            </div>
            Welcome, <?php echo htmlspecialchars($admin_email); ?>!
        </div>
        <a href="logout.php" class="sign-out-btn">
            <i class="fas fa-sign-out-alt me-2"></i>Sign Out
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Breadcrumb -->
        <div class="breadcrumb-container animate-fade-in">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="breadcrumb-item"><a href="folder_management.php"><i class="fas fa-folder me-1"></i>Folders</a></li>
                    <li class="breadcrumb-item"><a href="manage_folder.php?folder_id=<?php echo $folder_id; ?>"><i class="fas fa-folder-open me-1"></i><?php echo htmlspecialchars($file['FOLDER_NAME']); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($file_name); ?></li>
                </ol>
            </nav>
        </div>

        <!-- File Info Header -->
        <div class="preview-header">
            <div class="file-info">
                <?php
                $icon_class = 'fa-file';
                $type_class = '';
                
                switch ($file_extension) {
                    case 'pdf':
                        $icon_class = 'fa-file-pdf';
                        $type_class = 'pdf';
                        break;
                    case 'doc':
                    case 'docx':
                        $icon_class = 'fa-file-word';
                        $type_class = 'doc';
                        break;
                    case 'xls':
                    case 'xlsx':
                        $icon_class = 'fa-file-excel';
                        $type_class = 'xls';
                        break;
                    case 'ppt':
                    case 'pptx':
                        $icon_class = 'fa-file-powerpoint';
                        $type_class = 'ppt';
                        break;
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                        $icon_class = 'fa-file-image';
                        $type_class = 'img';
                        break;
                    case 'txt':
                    case 'csv':
                        $icon_class = 'fa-file-alt';
                        $type_class = 'txt';
                        break;
                }
                ?>
                <div class="file-icon-wrapper <?php echo $type_class; ?>">
                    <i class="fas <?php echo $icon_class; ?>"></i>
                </div>
                <div class="file-details">
                    <h1 class="file-name"><?php echo htmlspecialchars($file_name); ?></h1>
                    <div class="file-meta">
                        <span class="file-type-badge <?php echo $type_class; ?>">
                            <?php echo strtoupper($file_extension); ?>
                        </span>
                        <div class="file-meta-item">
                            <i class="fas fa-folder"></i>
                            <?php echo htmlspecialchars($file['FOLDER_NAME']); ?>
                        </div>
                        <div class="file-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo isset($file['timers']) ? date('M d, Y', strtotime($file['timers'])) : 'N/A'; ?>
                        </div>
                        <div class="file-meta-item">
                            <i class="fas fa-weight-hanging"></i>
                            <?php 
                            if (isset($file['size'])) {
                                $size = $file['size'];
                                if ($size > 1048576) {
                                    echo round($size / 1048576, 2) . ' MB';
                                } elseif ($size > 1024) {
                                    echo round($size / 1024, 2) . ' KB';
                                } else {
                                    echo $size . ' bytes';
                                }
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="action-buttons">
                <a href="manage_folder.php?folder_id=<?php echo $folder_id; ?>" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Folder</span>
                </a>
                <a href="downloadfolderfile.php?file_id=<?php echo $file_id; ?>" class="btn btn-primary" download>
                    <i class="fas fa-download"></i>
                    <span>Download</span>
                </a>
            </div>
        </div>

        <!-- Preview Container -->
        <div class="preview-container">
            <?php if ($viewer_type === 'image'): ?>
            <!-- Image Viewer -->
            <div class="preview-toolbar">
                <div class="toolbar-left">
                    <span class="toolbar-title">Image Preview</span>
                </div>
                <div class="toolbar-actions">
                    <button class="toolbar-btn" id="toggleFullscreen" title="Toggle Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="preview-content">
                <div class="preview-image-container">
                    <img src="<?php echo htmlspecialchars($file_path); ?>" class="preview-image" id="previewImage" alt="<?php echo htmlspecialchars($file_name); ?>">
                    <div class="image-controls">
                        <button class="image-control-btn" id="zoomOut" title="Zoom Out">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <span class="zoom-level" id="zoomLevel">100%</span>
                        <button class="image-control-btn" id="zoomIn" title="Zoom In">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <button class="image-control-btn" id="resetZoom" title="Reset Zoom">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <?php elseif ($viewer_type === 'pdf'): ?>
            <!-- PDF Viewer -->
            <div class="preview-toolbar">
                <div class="toolbar-left">
                    <span class="toolbar-title">PDF Document</span>
                </div>
                <div class="toolbar-actions">
                    <button class="toolbar-btn" id="toggleFullscreen" title="Toggle Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="preview-content" id="pdfViewerContainer">
                <iframe id="pdf-viewer" src="<?php echo htmlspecialchars($file_path); ?>" type="application/pdf"></iframe>
            </div>
            
            <?php elseif ($viewer_type === 'text'): ?>
            <!-- Text Viewer -->
            <div class="preview-toolbar">
                <div class="toolbar-left">
                    <span class="toolbar-title">Text Document</span>
                </div>
                <div class="toolbar-actions">
                    <button class="toolbar-btn" id="toggleFullscreen" title="Toggle Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="preview-content">
                <?php
                $text_content = file_get_contents($file_path);
                ?>
                <textarea class="preview-text" readonly><?php echo htmlspecialchars($text_content); ?></textarea>
            </div>
            
            <?php elseif ($viewer_type === 'office'): ?>
            <!-- Office Document Viewer -->
            <div class="preview-toolbar">
                <div class="toolbar-left">
                    <span class="toolbar-title">Office Document</span>
                </div>
                <div class="toolbar-actions">
                    <button class="toolbar-btn" id="toggleFullscreen" title="Toggle Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="preview-content">
                <?php
                $encoded_path = urlencode('https://' . $_SERVER['HTTP_HOST'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $file_path));
                ?>
                <div class="loading-overlay" id="officeLoading">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Loading document...</div>
                </div>
                <iframe class="office-viewer" id="officeViewer" src="https://docs.google.com/viewer?url=<?php echo $encoded_path; ?>&embedded=true"></iframe>
            </div>
            
            <?php else: ?>
            <!-- Unsupported File Format -->
            <div class="preview-content">
                <div class="unsupported-file">
                    <div class="unsupported-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="unsupported-title">Preview not available</h3>
                    <p class="unsupported-text">This file type cannot be previewed in the browser. Please download the file to view its contents.</p>
                    <a href="downloadfolderfile.php?file_id=<?php echo $file_id; ?>" class="btn btn-primary" download>
                        <i class="fas fa-download"></i>
                        <span>Download File</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>All rights Reserved &copy; <?php echo date('Y'); ?> Created By: BitKeep Management</p>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="modalRegisterForm" tabindex="-1" aria-labelledby="modalRegisterFormLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRegisterFormLabel"><i class="fas fa-user-plus me-2 text-primary"></i>Add Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="create_Admin.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="adminName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="adminName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="adminDepartment" class="form-label">Department</label>
                            <input type="text" class="form-control" id="adminDepartment" name="department" required>
                        </div>
                        <div class="mb-3">
                            <label for="adminEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="adminEmail" name="admin_user" required>
                        </div>
                        <div class="mb-3">
                            <label for="adminPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="adminPassword" name="admin_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="adminStatus" class="form-label">Status</label>
                            <input type="text" class="form-control" id="adminStatus" name="admin_status" value="Admin" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="reg">
                            <i class="fas fa-user-plus me-2"></i>Add Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="modalRegisterForm2" tabindex="-1" aria-labelledby="modalRegisterForm2Label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRegisterForm2Label"><i class="fas fa-user-plus me-2 text-primary"></i>Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="create_user.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="userName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="userName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="userEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="userEmail" name="email_address" required>
                        </div>
                        <div class="mb-3">
                            <label for="userDepartment" class="form-label">Department</label>
                            <input type="text" class="form-control" id="userDepartment" name="department" required>
                        </div>
                        <div class="mb-3">
                            <label for="userPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="userPassword" name="user_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="userStatus" class="form-label">Status</label>
                            <input type="text" class="form-control" id="userStatus" name="user_status" value="Employee" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="reguser">
                            <i class="fas fa-user-plus me-2"></i>Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Loader
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loader').style.display = 'none';
            }, 500);
        });

        // Mobile Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target) && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Toggle fullscreen functionality
        const toggleFullscreen = document.getElementById('toggleFullscreen');
        const previewContainer = document.querySelector('.preview-container');
        
        if (toggleFullscreen) {
            toggleFullscreen.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    if (previewContainer.requestFullscreen) {
                        previewContainer.requestFullscreen();
                    } else if (previewContainer.mozRequestFullScreen) { /* Firefox */
                        previewContainer.mozRequestFullScreen();
                    } else if (previewContainer.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
                        previewContainer.webkitRequestFullscreen();
                    } else if (previewContainer.msRequestFullscreen) { /* IE/Edge */
                        previewContainer.msRequestFullscreen();
                    }
                    toggleFullscreen.innerHTML = '<i class="fas fa-compress"></i>';
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    }
                    toggleFullscreen.innerHTML = '<i class="fas fa-expand"></i>';
                }
            });
        }
        
        // Image viewer zoom functionality
        const previewImage = document.getElementById('previewImage');
        const zoomIn = document.getElementById('zoomIn');
        const zoomOut = document.getElementById('zoomOut');
        const resetZoom = document.getElementById('resetZoom');
        const zoomLevel = document.getElementById('zoomLevel');
        
        if (previewImage && zoomIn && zoomOut && resetZoom && zoomLevel) {
            let currentZoom = 100;
            
            function updateZoom() {
                previewImage.style.transform = `scale(${currentZoom / 100})`;
                zoomLevel.textContent = `${currentZoom}%`;
            }
            
            zoomIn.addEventListener('click', function() {
                currentZoom += 10;
                if (currentZoom > 200) currentZoom = 200;
                updateZoom();
            });
            
            zoomOut.addEventListener('click', function() {
                currentZoom -= 10;
                if (currentZoom < 50) currentZoom = 50;
                updateZoom();
            });
            
            resetZoom.addEventListener('click', function() {
                currentZoom = 100;
                updateZoom();
            });
        }
        
        // Office viewer loading indicator
        const officeViewer = document.getElementById('officeViewer');
        const officeLoading = document.getElementById('officeLoading');
        
        if (officeViewer && officeLoading) {
            officeViewer.onload = function() {
                // Hide loading after a short delay to ensure content is rendered
                setTimeout(function() {
                    officeLoading.style.display = 'none';
                }, 1500);
            };
            
            // Fallback if iframe doesn't trigger onload
            setTimeout(function() {
                officeLoading.style.display = 'none';
            }, 8000);
        }
        
        <?php if ($viewer_type === 'pdf'): ?>
        // PDF.js viewer as fallback
        const pdfViewer = document.getElementById('pdf-viewer');
        const pdfViewerContainer = document.getElementById('pdfViewerContainer');
        
        // Check if the iframe content loaded correctly
        if (pdfViewer) {
            pdfViewer.onload = function() {
                // If the iframe content couldn't be loaded properly, switch to PDF.js rendering
                try {
                    if (pdfViewer.contentDocument && 
                        pdfViewer.contentDocument.body && 
                        pdfViewer.contentDocument.body.innerHTML.trim() === '') {
                        renderPDFWithPDFJS('<?php echo htmlspecialchars($file_path); ?>');
                    }
                } catch (e) {
                    // CORS error or other issue, switch to PDF.js
                    renderPDFWithPDFJS('<?php echo htmlspecialchars($file_path); ?>');
                }
            };
            
            // Fallback if iframe doesn't trigger onload or has issues
            setTimeout(function() {
                try {
                    if (pdfViewer.contentDocument && 
                        pdfViewer.contentDocument.body && 
                        pdfViewer.contentDocument.body.innerHTML.trim() === '') {
                        renderPDFWithPDFJS('<?php echo htmlspecialchars($file_path); ?>');
                    }
                } catch (e) {
                    renderPDFWithPDFJS('<?php echo htmlspecialchars($file_path); ?>');
                }
            }, 3000);
        }
        
        // Function to render PDF with PDF.js as a fallback
        function renderPDFWithPDFJS(url) {
            if (!pdfViewerContainer) return;
            
            // Create custom PDF viewer
            pdfViewerContainer.innerHTML = `
                <div class="pdf-controls">
                    <div class="pdf-page-controls">
                        <button class="toolbar-btn" id="prevPage">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="pdf-page-info" id="pageInfo">Page 1 of 1</span>
                        <button class="toolbar-btn" id="nextPage">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="toolbar-actions">
                        <button class="toolbar-btn" id="zoomOutPdf">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <span class="pdf-page-info" id="zoomInfo">100%</span>
                        <button class="toolbar-btn" id="zoomInPdf">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <button class="toolbar-btn" id="resetZoomPdf">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="pdf-container" id="pdfContainer">
                    <div class="loading-overlay" id="pdfLoading">
                        <div class="loading-spinner"></div>
                        <div class="loading-text">Loading PDF...</div>
                    </div>
                </div>
            `;
            
            const pdfContainer = document.getElementById('pdfContainer');
            const pageInfo = document.getElementById('pageInfo');
            const prevPage = document.getElementById('prevPage');
            const nextPage = document.getElementById('nextPage');
            const zoomOutPdf = document.getElementById('zoomOutPdf');
            const zoomInPdf = document.getElementById('zoomInPdf');
            const resetZoomPdf = document.getElementById('resetZoomPdf');
            const zoomInfo = document.getElementById('zoomInfo');
            const pdfLoading = document.getElementById('pdfLoading');
            
            let currentPage = 1;
            let pdfDoc = null;
            let scale = 1.5;
            
            // Load the PDF
            pdfjsLib.getDocument(url).promise.then(function(pdf) {
                pdfDoc = pdf;
                pageInfo.textContent = `Page ${currentPage} of ${pdf.numPages}`;
                
                // Render the first page
                renderPage(currentPage);
                
                // Hide loading indicator
                if (pdfLoading) pdfLoading.style.display = 'none';
                
                // Enable/disable page navigation buttons
                updatePageButtons();
            }).catch(function(error) {
                console.error('Error loading PDF:', error);
                if (pdfContainer) {
                    pdfContainer.innerHTML = `
                        <div class="unsupported-file">
                            <div class="unsupported-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h3 class="unsupported-title">Error Loading PDF</h3>
                            <p class="unsupported-text">There was an error loading the PDF. Please download the file to view it.</p>
                            <a href="downloadfolderfile.php?file_id=<?php echo $file_id; ?>" class="btn btn-primary" download>
                                <i class="fas fa-download"></i>
                                <span>Download File</span>
                            </a>
                        </div>
                    `;
                }
                if (pdfLoading) pdfLoading.style.display = 'none';
            });
            
            // Render a specific page
            function renderPage(pageNum) {
                pdfDoc.getPage(pageNum).then(function(page) {
                    const viewport = page.getViewport({ scale: scale });
                    
                    // Check if canvas for this page already exists
                    let canvas = document.getElementById(`page-${pageNum}`);
                    if (!canvas) {
                        canvas = document.createElement('canvas');
                        canvas.id = `page-${pageNum}`;
                        canvas.className = 'pdf-page';
                        pdfContainer.appendChild(canvas);
                    }
                    
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    
                    // Render PDF page
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    
                    page.render(renderContext);
                    
                    // Show only current page
                    const allPages = document.querySelectorAll('.pdf-page');
                    allPages.forEach(p => {
                        p.style.display = 'none';
                    });
                    canvas.style.display = 'block';
                    
                    // Scroll to top of container
                    pdfContainer.scrollTop = 0;
                });
            }
            
            // Update page navigation buttons
            function updatePageButtons() {
                if (prevPage) prevPage.disabled = currentPage <= 1;
                if (nextPage) nextPage.disabled = currentPage >= pdfDoc.numPages;
            }
            
            // Page navigation
            if (prevPage) {
                prevPage.addEventListener('click', function() {
                    if (currentPage <= 1) return;
                    currentPage--;
                    renderPage(currentPage);
                    pageInfo.textContent = `Page ${currentPage} of ${pdfDoc.numPages}`;
                    updatePageButtons();
                });
            }
            
            if (nextPage) {
                nextPage.addEventListener('click', function() {
                    if (currentPage >= pdfDoc.numPages) return;
                    currentPage++;
                    renderPage(currentPage);
                    pageInfo.textContent = `Page ${currentPage} of ${pdfDoc.numPages}`;
                    updatePageButtons();
                });
            }
            
            // Zoom controls
            if (zoomInPdf) {
                zoomInPdf.addEventListener('click', function() {
                    scale += 0.25;
                    if (scale > 3) scale = 3;
                    zoomInfo.textContent = `${Math.round(scale * 100 / 1.5)}%`;
                    renderPage(currentPage);
                });
            }
            
            if (zoomOutPdf) {
                zoomOutPdf.addEventListener('click', function() {
                    scale -= 0.25;
                    if (scale < 0.5) scale = 0.5;
                    zoomInfo.textContent = `${Math.round(scale * 100 / 1.5)}%`;
                    renderPage(currentPage);
                });
            }
            
            if (resetZoomPdf) {
                resetZoomPdf.addEventListener('click', function() {
                    scale = 1.5;
                    zoomInfo.textContent = '100%';
                    renderPage(currentPage);
                });
            }
        }
        <?php endif; ?>
    </script>
</body>
</html>



