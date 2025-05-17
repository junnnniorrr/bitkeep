<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION["email_address"])) {
    header("location:../login.html");
    exit();
}

// Include database connection
require_once("../include/connection.php");

// Get user email from database based on session ID
$user_id = $_SESSION["email_address"];
$stmt = $conn->prepare("SELECT email_address FROM login_user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_row = $result->fetch_assoc();
    $user_email = $user_row['email_address'];
} else {
    // User not found, redirect to login
    session_destroy();
    header("location:../login.html");
    exit();
}
$stmt->close();

// Check if file_id is provided
if (!isset($_GET['file_id']) || empty($_GET['file_id'])) {
    $_SESSION['error'] = "No file specified.";
    header("location:user_dashboard.php");
    exit();
}

$file_id = $_GET['file_id'];

// Get file information and verify user has access to it
$stmt = $conn->prepare("SELECT ff.*, fr.user_email 
                       FROM folder_files ff
                       JOIN folders f ON ff.folder_id = f.folder_id
                       JOIN folder_requests fr ON ff.folder_id = fr.assigned_folder_id
                       WHERE ff.id = ? AND fr.user_email = ? AND fr.status = 'Approved'");
$stmt->bind_param("is", $file_id, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Either file doesn't exist or user doesn't have access
    $_SESSION['error'] = "You don't have access to this file.";
    if (isset($_GET['folder_id'])) {
        header("location:manage_folder.php?folder_id=" . $_GET['folder_id']);
    } else {
        header("location:user_dashboard.php");
    }
    exit();
}

$file_info = $result->fetch_assoc();
$stmt->close();

// Get file extension
$file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));

// Log the file view
$current_time = date("Y-m-d H:i:s");
$log_stmt = $conn->prepare("INSERT INTO file_access_logs (file_id, user_email, access_type, access_time) VALUES (?, ?, 'preview', ?)");
$log_stmt->bind_param("iss", $file_id, $user_email, $current_time);
$log_stmt->execute();
$log_stmt->close();

// Function to get file MIME type based on extension
function getMimeType($extension) {
    $mime_types = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'html' => 'text/html',
        'htm' => 'text/html',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ];
    
    return isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream';
}

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
$file_path = $file_info['file_path'];
$file_name = $file_info['name'];
$folder_id = $file_info['folder_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - <?php echo htmlspecialchars($file_name); ?></title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- PDF.js for PDF preview -->
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
            --sidebar-width: 250px;
            --navbar-height: 70px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: var(--navbar-height);
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            height: calc(100vh - var(--navbar-height));
            width: var(--sidebar-width);
            background-color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            z-index: 999;
            transition: all 0.3s;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--gray-200);
            text-align: center;
        }
        
        .sidebar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--dark-color);
            text-decoration: none;
        }
        
        .highlight {
            color: var(--secondary-color);
        }
        
        .sidebar-menu {
            padding: 15px 0;
            margin-bottom: 70px;
        }
        
        .sidebar-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 4px solid transparent;
            font-weight: 500;
        }
        
        .sidebar-item:hover, .sidebar-item.active {
            background-color: var(--gray-100);
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }
        
        .sidebar-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-divider {
            height: 1px;
            background-color: var(--gray-200);
            margin: 10px 20px;
        }
        
        .sidebar-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--gray-200);
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: white;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px 20px;
            background-color: var(--gray-100);
            border-radius: var(--border-radius);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-weight: bold;
        }
        
        .user-details {
            flex: 1;
            overflow: hidden;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--gray-800);
        }
        
        .user-role {
            font-size: 0.75rem;
            color: var(--gray-500);
        }
        
        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all 0.3s;
            min-height: calc(100vh - var(--navbar-height));
            width: calc(100% - var(--sidebar-width));
        }
        
        /* Navbar Styles */
        .navbar {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: white !important;
            padding: 0 30px;
            height: var(--navbar-height);
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        /* Toggle Button */
        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--gray-700);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius);
            transition: all 0.2s;
        }
        
        .sidebar-toggle:hover {
            background-color: var(--gray-100);
        }
        
        .app-container {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            flex: 1;
        }
        
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
        
        .breadcrumb-container {
            margin-bottom: 1.5rem;
        }
        
        .breadcrumb {
            display: flex;
            flex-wrap: wrap;
            padding: 0;
            margin: 0;
            list-style: none;
            background-color: transparent;
        }
        
        .breadcrumb-item {
            display: flex;
            align-items: center;
        }
        
        .breadcrumb-item a {
            color: var(--gray-500);
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.875rem;
        }
        
        .breadcrumb-item a:hover {
            color: var(--primary-color);
        }
        
        .breadcrumb-item.active {
            color: var(--gray-800);
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
            padding: 0 0.5rem;
            color: var(--gray-400);
        }
        
        .footer {
            text-align: center;
            padding: 1.5rem;
            color: var(--gray-500);
            font-size: 0.875rem;
            background-color: white;
            border-top: 1px solid var(--gray-200);
            margin-top: auto;
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
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
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content, .navbar, .footer {
                margin-left: 0;
                width: 100%;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .app-container {
                padding: 1.5rem;
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
        }
        
        @media (max-width: 768px) {
            .app-container {
                padding: 1rem;
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
        
        /* Custom PDF viewer styles to hide print and download buttons */
        /* This creates a wrapper for our custom PDF viewer */
        .custom-pdf-wrapper {
            position: relative;
            width: 100%;
            height: 80vh;
        }
        
        /* The actual PDF viewer iframe */
        #pdf-viewer {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Overlay to intercept clicks on print/download buttons */
        .pdf-toolbar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 40px; /* Adjust based on the toolbar height */
            z-index: 1000;
            pointer-events: none; /* Allow clicks to pass through by default */
        }
        
        /* These are positioned blocks that will intercept clicks only on the print/download buttons */
        .block-print-button, .block-download-button {
            position: absolute;
            top: 0;
            height: 40px;
            width: 40px;
            pointer-events: auto; /* Block clicks on these specific areas */
            background: transparent;
        }
        
        /* Position these based on common PDF.js toolbar layouts */
        .block-print-button {
            right: 80px; /* Adjust based on your PDF.js viewer */
        }
        
        .block-download-button {
            right: 40px; /* Adjust based on your PDF.js viewer */
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <button class="sidebar-toggle me-3" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <span class="navbar-brand">
                <span class="highlight">Bit</span>Keep <span class="highlight">M</span>anagement <span class="highlight">S</span>ystem
            </span>
            <div class="d-flex ms-auto">
                <span class="navbar-text">
                    <i class="fas fa-calendar-day me-2"></i> <?php echo date('F d, Y'); ?>
                </span>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo ucwords(htmlentities($user_email)); ?></div>
                <div class="user-role">User</div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="home.php" class="sidebar-item">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="add_file.php" class="sidebar-item">
                <i class="fas fa-file-medical"></i> Add File
            </a>
            <a href="history_log.php" class="sidebar-item">
                <i class="fas fa-history"></i> User Logs
            </a>
            <a href="request_folder.php" class="sidebar-item">
                <i class="fas fa-folder-plus"></i> Request Folder
            </a>
            <a href="user_dashboard.php" class="sidebar-item active">
                <i class="fas fa-folder"></i> My Folders
            </a>
            
            <div class="sidebar-divider"></div>
            
            <a href="Logout.php" class="sidebar-item">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </div>
        
        <div class="sidebar-footer">
            <small class="text-muted">© 2025 BitKeep Management System</small>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="app-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb-container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="user_dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="manage_folder.php?folder_id=<?php echo $folder_id; ?>">Back to Folder</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($file_name); ?></li>
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
                                <i class="fas fa-weight-hanging"></i>
                                <?php 
                                $size_in_kb = round($file_info['size'] / 1024, 2);
                                $size_in_mb = round($size_in_kb / 1024, 2);
                                $formatted_size = $size_in_mb >= 1 ? $size_in_mb . " MB" : $size_in_kb . " KB";
                                echo $formatted_size;
                                ?>
                            </div>
                            <div class="file-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('M d, Y', strtotime($file_info['timers'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="manage_folder.php?folder_id=<?php echo $folder_id; ?>" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Folder</span>
                    </a>
                    <!-- Modified download button to use the new download handler -->
                    <a href="download_file.php?file_id=<?php echo $file_id; ?>" class="btn btn-primary">
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
                    <!-- Modified PDF viewer with wrapper and overlay -->
                    <div class="custom-pdf-wrapper">
                        <!-- Overlay to block specific buttons -->
                        <div class="pdf-toolbar-overlay">
                            <div class="block-print-button"></div>
                            <div class="block-download-button"></div>
                        </div>
                        <!-- Use a custom URL parameter to signal we want to hide buttons -->
                        <iframe id="pdf-viewer" src="<?php echo htmlspecialchars($file_path); ?>#toolbar=1&navpanes=1&scrollbar=1&view=FitH&hideprint=true&hidedownload=true" type="application/pdf"></iframe>
                    </div>
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
                        <a href="download_file.php?file_id=<?php echo $file_id; ?>" class="btn btn-primary">
                            <i class="fas fa-download"></i>
                            <span>Download File</span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p class="mb-0">BitKeep Management System &copy; 2025. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // Close sidebar on small screens when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth < 992 && 
                    !sidebar.contains(e.target) && 
                    e.target !== sidebarToggle && 
                    !sidebarToggle.contains(e.target) && 
                    sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            });
        }
        
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
        
        // Responsive behavior
        function checkSize() {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('active');
            }
        }
        
        // Check on resize
        window.addEventListener('resize', checkSize);
        
        // Check on load
        checkSize();
        
        <?php if ($viewer_type === 'pdf'): ?>
        // PDF.js viewer as fallback
        const pdfViewer = document.getElementById('pdf-viewer');
        const pdfViewerContainer = document.getElementById('pdfViewerContainer');
        
        // Enhanced PDF.js button hiding
        if (pdfViewer) {
            // Function to hide print and download buttons in PDF.js viewer
            function hidePdfButtons() {
                try {
                    // Try to access the iframe content
                    const iframeDoc = pdfViewer.contentDocument || pdfViewer.contentWindow.document;
                    
                    if (iframeDoc) {
                        // Method 1: Inject CSS to hide buttons
                        const style = document.createElement('style');
                        style.textContent = `
                            /* Target buttons by various selectors */
                            button[data-l10n-id="print"],
                            button[data-l10n-id="download"],
                            button[aria-label="Print"],
                            button[aria-label="Download"],
                            #print,
                            #download,
                            .print,
                            .download,
                            .toolbarButton.print,
                            .toolbarButton.download,
                            #secondaryPrint,
                            #secondaryDownload,
                            .secondaryToolbarButton.print,
                            .secondaryToolbarButton.download,
                            [title="Print"],
                            [title="Download"] {
                                display: none !important;
                                visibility: hidden !important;
                                opacity: 0 !important;
                                pointer-events: none !important;
                            }
                            
                            /* Target by position if specific selectors fail */
                            .toolbarButton:nth-last-child(1),
                            .toolbarButton:nth-last-child(2) {
                                display: none !important;
                            }
                        `;
                        
                        iframeDoc.head.appendChild(style);
                        
                        // Method 2: Direct DOM manipulation
                        const selectors = [
                            'button[data-l10n-id="print"]',
                            'button[data-l10n-id="download"]',
                            'button[aria-label="Print"]',
                            'button[aria-label="Download"]',
                            '#print',
                            '#download',
                            '.print',
                            '.download',
                            '.toolbarButton.print',
                            '.toolbarButton.download',
                            '#secondaryPrint',
                            '#secondaryDownload',
                            '.secondaryToolbarButton.print',
                            '.secondaryToolbarButton.download',
                            '[title="Print"]',
                            '[title="Download"]'
                        ];
                        
                        selectors.forEach(selector => {
                            const elements = iframeDoc.querySelectorAll(selector);
                            elements.forEach(el => {
                                el.style.display = 'none';
                                el.style.visibility = 'hidden';
                                el.disabled = true;
                                el.setAttribute('aria-hidden', 'true');
                                // Remove event listeners by cloning and replacing
                                const clone = el.cloneNode(true);
                                if (el.parentNode) {
                                    el.parentNode.replaceChild(clone, el);
                                }
                            });
                        });
                        
                        // Method 3: Find buttons by their icon content
                        const allButtons = iframeDoc.querySelectorAll('button');
                        allButtons.forEach(button => {
                            // Check if button contains print or download icons
                            const buttonText = button.textContent.toLowerCase();
                            const buttonHTML = button.innerHTML.toLowerCase();
                            if (
                                buttonText.includes('print') || 
                                buttonText.includes('download') ||
                                buttonHTML.includes('print') || 
                                buttonHTML.includes('download')
                            ) {
                                button.style.display = 'none';
                                button.style.visibility = 'hidden';
                                button.disabled = true;
                                button.setAttribute('aria-hidden', 'true');
                            }
                        });
                        
                        // Method 4: Set up a mutation observer to catch dynamically added buttons
                        const observer = new MutationObserver(function() {
                            // Re-run our button hiding logic
                            selectors.forEach(selector => {
                                const elements = iframeDoc.querySelectorAll(selector);
                                elements.forEach(el => {
                                    el.style.display = 'none';
                                    el.style.visibility = 'hidden';
                                    el.disabled = true;
                                });
                            });
                        });
                        
                        // Start observing the document with the configured parameters
                        observer.observe(iframeDoc.body, { 
                            childList: true, 
                            subtree: true,
                            attributes: true,
                            attributeFilter: ['style', 'class']
                        });
                        
                        // Method 5: Override print and save functions
                        try {
                            if (pdfViewer.contentWindow) {
                                // Override print function
                                pdfViewer.contentWindow.print = function() {
                                    console.log('Print function blocked');
                                    return false;
                                };
                                
                                // Try to access the PDFViewerApplication object
                                if (pdfViewer.contentWindow.PDFViewerApplication) {
                                    const app = pdfViewer.contentWindow.PDFViewerApplication;
                                    
                                    // Override download methods if they exist
                                    if (app.download) {
                                        app.download = function() {
                                            console.log('Download function blocked');
                                            return false;
                                        };
                                    }
                                    
                                    if (app.downloadOrSave) {
                                        app.downloadOrSave = function() {
                                            console.log('Download function blocked');
                                            return false;
                                        };
                                    }
                                }
                            }
                        } catch (e) {
                            console.log('Could not override print/save functions', e);
                        }
                    }
                } catch (e) {
                    console.error('Error hiding PDF buttons:', e);
                }
            }
            
            // Position the blocking overlays more precisely
            function positionBlockers() {
                try {
                    const iframeDoc = pdfViewer.contentDocument || pdfViewer.contentWindow.document;
                    
                    if (iframeDoc) {
                        // Find the print and download buttons
                        const printButton = iframeDoc.querySelector('button[data-l10n-id="print"], .print, .toolbarButton.print, button[aria-label="Print"]');
                        const downloadButton = iframeDoc.querySelector('button[data-l10n-id="download"], .download, .toolbarButton.download, button[aria-label="Download"]');
                        
                        if (printButton) {
                            const rect = printButton.getBoundingClientRect();
                            const blockPrint = document.querySelector('.block-print-button');
                            if (blockPrint) {
                                blockPrint.style.right = 'auto';
                                blockPrint.style.left = `${rect.left}px`;
                                blockPrint.style.width = `${rect.width}px`;
                                blockPrint.style.height = `${rect.height}px`;
                            }
                        }
                        
                        if (downloadButton) {
                            const rect = downloadButton.getBoundingClientRect();
                            const blockDownload = document.querySelector('.block-download-button');
                            if (blockDownload) {
                                blockDownload.style.right = 'auto';
                                blockDownload.style.left = `${rect.left}px`;
                                blockDownload.style.width = `${rect.width}px`;
                                blockDownload.style.height = `${rect.height}px`;
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error positioning blockers:', e);
                }
            }
            
            // Try multiple times to hide buttons and position blockers
            function attemptToHideButtons() {
                hidePdfButtons();
                positionBlockers();
                
                // Try again after short delays to catch late-loaded elements
                setTimeout(hidePdfButtons, 500);
                setTimeout(hidePdfButtons, 1000);
                setTimeout(hidePdfButtons, 2000);
                
                setTimeout(positionBlockers, 500);
                setTimeout(positionBlockers, 1000);
                setTimeout(positionBlockers, 2000);
            }
            
            // Call when iframe loads
            pdfViewer.onload = attemptToHideButtons;
            
            // Also try immediately
            attemptToHideButtons();
            
            // Add event listeners to the blocking overlays
            const blockPrint = document.querySelector('.block-print-button');
            const blockDownload = document.querySelector('.block-download-button');
            
            if (blockPrint) {
                blockPrint.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Print button click blocked');
                    return false;
                });
            }
            
            if (blockDownload) {
                blockDownload.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Download button click blocked');
                    return false;
                });
            }
            
            // Check if the iframe content loaded correctly
            setTimeout(function() {
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
            }, 3000);
        }
        
        // Function to render PDF with PDF.js as a fallback (without print/download buttons)
        function renderPDFWithPDFJS(url) {
            if (!pdfViewerContainer) return;
            
            // Create custom PDF viewer (without print/download buttons)
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
            const zoomOutPdf =  document.getElementById('zoomOutPdf');
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
                            <a href="download_file.php?file_id=<?php echo $file_id; ?>" class="btn btn-primary">
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
    });
    </script>
</body>
</html>