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

// Generate a secure token for this viewing session
function generateSecureToken() {
    return bin2hex(random_bytes(16));
}

// Create a secure token for this viewing session
$secure_token = generateSecureToken();

// Store the token in the session for verification
$_SESSION['file_view_token'] = $secure_token;

// Get viewer type for this file
$viewer_type = getViewerType($file_extension);
$file_path = $file_info['file_path'];
$file_name = $file_info['name'];
$folder_id = $file_info['folder_id'];

// For secure file access, we'll create a proxy endpoint
$secure_viewer_url = "secure_viewer.php?file_id={$file_id}&token={$secure_token}";
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
        
        /* Secure Canvas Container Styles */
        .secure-canvas-container {
            width: 100%;
            height: 80vh;
            overflow: auto;
            background-color: var(--gray-100);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .secure-canvas {
            display: block;
            margin: 20px auto;
            background-color: white;
            box-shadow: var(--box-shadow);
        }
        
        .watermark-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
            opacity: 0.5;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-45deg);
            font-size: 3rem;
            color: rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .watermark-text {
            white-space: nowrap;
            user-select: none;
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
            z-index: 20;
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
            position: relative;
            z-index: 5;
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
            z-index: 30;
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
        
        /* Security notification */
        .security-notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            border-radius: 5px;
            z-index: 9999;
            text-align: center;
            font-weight: 500;
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
            
            .secure-canvas-container {
                height: 70vh;
            }
            
            .preview-text {
                height: 70vh;
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
                <!-- Secure Image Viewer - Using Canvas instead of direct img tag -->
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
                    <div class="secure-canvas-container" id="imageContainer">
                        <canvas id="secureImageCanvas" class="secure-canvas"></canvas>
                        <div class="watermark-overlay">
                            <div class="watermark-text"><?php echo htmlspecialchars($user_email); ?> - <?php echo date('Y-m-d H:i:s'); ?></div>
                        </div>
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
                        <div class="loading-overlay" id="imageLoading">
                            <div class="loading-spinner"></div>
                            <div class="loading-text">Loading image...</div>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($viewer_type === 'pdf'): ?>
                <!-- Secure PDF Viewer - Using Canvas-based rendering -->
                <div class="preview-toolbar">
                    <div class="toolbar-left">
                        <span class="toolbar-title">PDF Document</span>
                    </div>
                    <div class="toolbar-actions">
                        <button class="toolbar-btn" id="prevPage" title="Previous Page">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="pdf-page-info" id="pageInfo">Page 1</span>
                        <button class="toolbar-btn" id="nextPage" title="Next Page">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button class="toolbar-btn" id="zoomOut" title="Zoom Out">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <span class="zoom-level" id="zoomLevel">100%</span>
                        <button class="toolbar-btn" id="zoomIn" title="Zoom In">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <button class="toolbar-btn" id="toggleFullscreen" title="Toggle Fullscreen">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="preview-content">
                    <div class="secure-canvas-container" id="pdfContainer">
                        <canvas id="securePdfCanvas" class="secure-canvas"></canvas>
                        <div class="watermark-overlay">
                            <div class="watermark-text"><?php echo htmlspecialchars($user_email); ?> - <?php echo date('Y-m-d H:i:s'); ?></div>
                        </div>
                        <div class="loading-overlay" id="pdfLoading">
                            <div class="loading-spinner"></div>
                            <div class="loading-text">Loading PDF...</div>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($viewer_type === 'text'): ?>
                <!-- Text Viewer - Keep as is but add security -->
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
                    <div class="secure-canvas-container">
                        <?php
                        $text_content = file_get_contents($file_path);
                        ?>
                        <div class="watermark-overlay">
                            <div class="watermark-text"><?php echo htmlspecialchars($user_email); ?> - <?php echo date('Y-m-d H:i:s'); ?></div>
                        </div>
                        <textarea class="preview-text" readonly><?php echo htmlspecialchars($text_content); ?></textarea>
                    </div>
                </div>
                
                <?php elseif ($viewer_type === 'office'): ?>
                <!-- Office Document Viewer - Using server-generated previews -->
                <div class="preview-toolbar">
                    <div class="toolbar-left">
                        <span class="toolbar-title">Office Document</span>
                    </div>
                    <div class="toolbar-actions">
                        <button class="toolbar-btn" id="prevPage" title="Previous Page">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="pdf-page-info" id="pageInfo">Page 1</span>
                        <button class="toolbar-btn" id="nextPage" title="Next Page">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button class="toolbar-btn" id="toggleFullscreen" title="Toggle Fullscreen">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="preview-content">
                    <div class="secure-canvas-container" id="officeContainer">
                        <canvas id="secureOfficeCanvas" class="secure-canvas"></canvas>
                        <div class="watermark-overlay">
                            <div class="watermark-text"><?php echo htmlspecialchars($user_email); ?> - <?php echo date('Y-m-d H:i:s'); ?></div>
                        </div>
                        <div class="loading-overlay" id="officeLoading">
                            <div class="loading-spinner"></div>
                            <div class="loading-text">Loading document...</div>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Unsupported File Format - Keep as is -->
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
        // Function to log security events to the database
        function logSecurityEventToDatabase(eventType, details = '') {
            // Create form data
            const formData = new FormData();
            formData.append('file_id', '<?php echo $file_id; ?>');
            formData.append('event_type', eventType);
            formData.append('details', details);
            
            // Send the data using fetch API
            fetch('log_security_event.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Security event logged successfully');
            })
            .catch(error => {
                console.error('Error logging security event:', error);
            });
        }
        
        // Global security functions
        function showNotification(message) {
            // Create notification element
            var notification = document.createElement('div');
            notification.className = 'security-notification';
            notification.style.position = 'fixed';
            notification.style.top = '50%';
            notification.style.left = '50%';
            notification.style.transform = 'translate(-50%, -50%)';
            notification.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
            notification.style.color = 'white';
            notification.style.padding = '20px';
            notification.style.borderRadius = '5px';
            notification.style.zIndex = '9999';
            notification.textContent = message;
            
            // Add to body
            document.body.appendChild(notification);
            
            // Remove after delay
            setTimeout(function() {
                document.body.removeChild(notification);
            }, 3000);
        }
        
        function applySecurityMeasures(element) {
            // Prevent right-click
            element.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                showNotification("Right-click is disabled.");
                logSecurityEventToDatabase('right_click', 'User attempted right-click');
                return false;
            });
            
            // Prevent keyboard shortcuts
            element.addEventListener('keydown', function(e) {
                if (e.keyCode == 123 || // F12
                    (e.ctrlKey && e.shiftKey && e.keyCode == 73) || // Ctrl+Shift+I
                    (e.ctrlKey && e.shiftKey && e.keyCode == 74) || // Ctrl+Shift+J
                    (e.ctrlKey && e.keyCode == 83) || // Ctrl+S
                    (e.ctrlKey && e.keyCode == 85) || // Ctrl+U
                    (e.keyCode == 44)) { // Print Screen
                    e.preventDefault();
                    showNotification("This action is disabled.");
                    
                    let shortcutType = '';
                    if (e.keyCode == 123) shortcutType = 'F12';
                    else if (e.ctrlKey && e.shiftKey && e.keyCode == 73) shortcutType = 'Ctrl+Shift+I';
                    else if (e.ctrlKey && e.shiftKey && e.keyCode == 74) shortcutType = 'Ctrl+Shift+J';
                    else if (e.ctrlKey && e.keyCode == 83) shortcutType = 'Ctrl+S';
                    else if (e.ctrlKey && e.keyCode == 85) shortcutType = 'Ctrl+U';
                    else if (e.keyCode == 44) shortcutType = 'Print Screen';
                    
                    logSecurityEventToDatabase('keyboard_shortcut', `User attempted keyboard shortcut: ${shortcutType}`);
                    return false;
                }
            });
            
            // Clear clipboard on Print Screen
            element.addEventListener('keyup', function(e) {
                if (e.keyCode == 44) {
                    navigator.clipboard.writeText('');
                    showNotification("Print Screen is disabled.");
                    logSecurityEventToDatabase('screenshot_attempt', 'User attempted Print Screen');
                }
            });
            
            // Disable text selection
            element.addEventListener('selectstart', function(e) {
                e.preventDefault();
                showNotification("Text selection is disabled.");
                logSecurityEventToDatabase('text_selection', 'User attempted to select text');
                return false;
            });
            
            // Disable drag and drop
            element.addEventListener('dragstart', function(e) {
                e.preventDefault();
                showNotification("Dragging is disabled.");
                logSecurityEventToDatabase('drag_attempt', 'User attempted to drag content');
                return false;
            });
        }
        
        // DevTools detection
        (function() {
            let devtools = {
                open: false,
                orientation: null
            };
            
            const threshold = 160;
            const emitEvent = (open, orientation) => {
                window.dispatchEvent(new CustomEvent('devtoolschange', {
                    detail: {
                        open,
                        orientation
                    }
                }));
            };
            
            // Check for width/height changes
            setInterval(() => {
                const widthThreshold = window.outerWidth - window.innerWidth > threshold;
                const heightThreshold = window.outerHeight - window.innerHeight > threshold;
                const orientation = widthThreshold ? 'vertical' : 'horizontal';
                
                if (
                    !(heightThreshold && widthThreshold) &&
                    ((window.Firebug && window.Firebug.chrome && window.Firebug.chrome.isInitialized) || widthThreshold || heightThreshold)
                ) {
                    if (!devtools.open || devtools.orientation !== orientation) {
                        devtools.open = true;
                        devtools.orientation = orientation;
                        emitEvent(true, orientation);
                        showNotification("Developer tools are not allowed.");
                        logSecurityEventToDatabase('devtools_open', `User opened developer tools (${orientation})`);
                    }
                } else {
                    if (devtools.open) {
                        devtools.open = false;
                        devtools.orientation = null;
                        emitEvent(false, null);
                    }
                }
            }, 500);
            
            // Listen for devtools event
            window.addEventListener('devtoolschange', function(e) {
                if (e.detail.open) {
                    showNotification("Developer tools are not allowed.");
                }
            });
            
            // Check if DevTools is already open when page loads
            if (
                window.outerWidth - window.innerWidth > threshold ||
                window.outerHeight - window.innerHeight > threshold
            ) {
                devtools.open = true;
                devtools.orientation = window.outerWidth - window.innerWidth > threshold ? 'vertical' : 'horizontal';
                emitEvent(true, devtools.orientation);
                showNotification("Developer tools are not allowed.");
                setTimeout(() => {
                    logSecurityEventToDatabase('devtools_open', `Developer tools were already open (${devtools.orientation})`);
                }, 1000);
            }
        })();
        
        // Apply security to the entire document
        document.addEventListener('DOMContentLoaded', function() {
            applySecurityMeasures(document);
            
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
            
            <?php if ($viewer_type === 'image'): ?>
            // Secure Image Viewer Implementation
            const canvas = document.getElementById('secureImageCanvas');
            const ctx = canvas.getContext('2d');
            const container = document.getElementById('imageContainer');
            const zoomIn = document.getElementById('zoomIn');
            const zoomOut = document.getElementById('zoomOut');
            const resetZoom = document.getElementById('resetZoom');
            const zoomLevel = document.getElementById('zoomLevel');
            const imageLoading = document.getElementById('imageLoading');
            
            let img = new Image();
            let currentZoom = 1;
            let isLoading = true;
            
            // Apply security to the canvas container
            applySecurityMeasures(container);
            
            // Additional security for image viewer
            container.addEventListener('mousedown', function(e) {
                // Check if it's a middle-click (which can be used to open image in new tab)
                if (e.button === 1) {
                    e.preventDefault();
                    showNotification("Middle-click is disabled.");
                    logSecurityEventToDatabase('middle_click', 'User attempted middle-click on image');
                    return false;
                }
            });
            
            // Load image securely
            img.crossOrigin = "anonymous";
            img.onload = function() {
                isLoading = false;
                if (imageLoading) imageLoading.style.display = 'none';
                drawImageToCanvas();
            };
            img.onerror = function() {
                if (imageLoading) imageLoading.style.display = 'none';
                showNotification("Error loading image.");
            };
            img.src = '<?php echo $secure_viewer_url; ?>';
            
            function drawImageToCanvas() {
                // Calculate dimensions
                const containerWidth = container.clientWidth;
                const containerHeight = container.clientHeight;
                
                // Set canvas size based on image and zoom
                const canvasWidth = img.width * currentZoom;
                const canvasHeight = img.height * currentZoom;
                
                canvas.width = canvasWidth;
                canvas.height = canvasHeight;
                
                // Clear canvas
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Draw image with current zoom
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                
                // Add watermark text
                addWatermark(ctx, canvas.width, canvas.height);
            }
            
            function addWatermark(ctx, width, height) {
                const text = '<?php echo htmlspecialchars($user_email); ?> - <?php echo date('Y-m-d H:i:s'); ?>';
                
                ctx.save();
                ctx.globalAlpha = 0.1;
                ctx.font = '20px Arial';
                ctx.fillStyle = '#000000';
                ctx.translate(width/2, height/2);
                ctx.rotate(-Math.PI/4);
                ctx.textAlign = 'center';
                ctx.fillText(text, 0, 0);
                ctx.restore();
            }
            
            // Zoom controls
            if (zoomIn) {
                zoomIn.addEventListener('click', function() {
                    currentZoom += 0.1;
                    if (currentZoom > 3) currentZoom = 3;
                    zoomLevel.textContent = `${Math.round(currentZoom * 100)}%`;
                    drawImageToCanvas();
                });
            }
            
            if (zoomOut) {
                zoomOut.addEventListener('click', function() {
                    currentZoom -= 0.1;
                    if (currentZoom < 0.5) currentZoom = 0.5;
                    zoomLevel.textContent = `${Math.round(currentZoom * 100)}%`;
                    drawImageToCanvas();
                });
            }
            
            if (resetZoom) {
                resetZoom.addEventListener('click', function() {
                    currentZoom = 1;
                    zoomLevel.textContent = '100%';
                    drawImageToCanvas();
                });
            }
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (!isLoading) {
                    drawImageToCanvas();
                }
            });
            <?php endif; ?>
            
            <?php if ($viewer_type === 'pdf'): ?>
            // Secure PDF Viewer Implementation
            const canvas = document.getElementById('securePdfCanvas');
            const ctx = canvas.getContext('2d');
            const container = document.getElementById('pdfContainer');
            const prevPage = document.getElementById('prevPage');
            const nextPage = document.getElementById('nextPage');
            const pageInfo = document.getElementById('pageInfo');
            const zoomIn = document.getElementById('zoomIn');
            const zoomOut = document.getElementById('zoomOut');
            const zoomLevel = document.getElementById('zoomLevel');
            const pdfLoading = document.getElementById('pdfLoading');
            
            let pdfDoc = null;
            let currentPage = 1;
            let currentZoom = 1.5;
            
            // Apply security to the PDF container
            applySecurityMeasures(container);
            
            // Additional security for PDF viewer
            container.addEventListener('mousedown', function(e) {
                // Check if it's a middle-click
                if (e.button === 1) {
                    e.preventDefault();
                    showNotification("Middle-click is disabled.");
                    logSecurityEventToDatabase('middle_click', 'User attempted middle-click on PDF');
                    return false;
                }
            });
            
            // Load the PDF securely using PDF.js
            pdfjsLib.getDocument('<?php echo $secure_viewer_url; ?>&type=pdf').promise.then(function(pdf) {
                pdfDoc = pdf;
                if (pageInfo) pageInfo.textContent = `Page ${currentPage} of ${pdf.numPages}`;
                
                // Render the first page
                renderPage(currentPage);
                
                // Hide loading indicator
                if (pdfLoading) pdfLoading.style.display = 'none';
                
                // Enable/disable page navigation buttons
                updatePageButtons();
            }).catch(function(error) {
                console.error('Error loading PDF:', error);
                if (pdfLoading) pdfLoading.style.display = 'none';
                
                // Show error message
                container.innerHTML = `
                    <div class="unsupported-file">
                        <div class="unsupported-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="unsupported-title">Error Loading PDF</h3>
                        <p class="unsupported-text">There was an error loading the PDF. Please try again later.</p>
                    </div>
                `;
            });
            
            // Render a specific page
            function renderPage(pageNum) {
                pdfDoc.getPage(pageNum).then(function(page) {
                    const viewport = page.getViewport({ scale: currentZoom });
                    
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    
                    // Render PDF page
                    const renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    
                    page.render(renderContext).promise.then(function() {
                        // Add watermark after rendering
                        addWatermark(ctx, canvas.width, canvas.height);
                        
                        // Scroll to top of container
                        container.scrollTop = 0;
                    });
                });
            }
            
            function addWatermark(ctx, width, height) {
                const text = '<?php echo htmlspecialchars($user_email); ?> - <?php echo date('Y-m-d H:i:s'); ?>';
                
                ctx.save();
                ctx.globalAlpha = 0.1;
                ctx.font = '20px Arial';
                ctx.fillStyle = '#000000';
                ctx.translate(width/2, height/2);
                ctx.rotate(-Math.PI/4);
                ctx.textAlign = 'center';
                ctx.fillText(text, 0, 0);
                ctx.restore();
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
            if (zoomIn) {
                zoomIn.addEventListener('click', function() {
                    currentZoom += 0.25;
                    if (currentZoom > 3) currentZoom = 3;
                    zoomLevel.textContent = `${Math.round(currentZoom * 100 / 1.5)}%`;
                    renderPage(currentPage);
                });
            }
            
            if (zoomOut) {
                zoomOut.addEventListener('click', function() {
                    currentZoom -= 0.25;
                    if (currentZoom < 0.5) currentZoom = 0.5;
                    zoomLevel.textContent = `${Math.round(currentZoom * 100 / 1.5)}%`;
                    renderPage(currentPage);
                });
            }
            <?php endif; ?>
            
            <?php if ($viewer_type === 'office'): ?>
            // Secure Office Document Viewer Implementation
            const canvas = document.getElementById('secureOfficeCanvas');
            const ctx = canvas.getContext('2d');
            const container = document.getElementById('officeContainer');
            const prevPage = document.getElementById('prevPage');
            const nextPage = document.getElementById('nextPage');
            const pageInfo = document.getElementById('pageInfo');
            const officeLoading = document.getElementById('officeLoading');
            
            let currentPage = 1;
            let totalPages = 1;
            let pageImages = [];
            
            // Apply security to the office container
            applySecurityMeasures(container);
            
            // Load office document previews
            fetch('<?php echo $secure_viewer_url; ?>&type=office')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        pageImages = data.pages;
                        totalPages = pageImages.length;
                        
                        if (pageInfo) pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
                        
                        // Render the first page
                        renderPage(currentPage);
                        
                        // Hide loading indicator
                        if (officeLoading) officeLoading.style.display = 'none';
                        
                        // Enable/disable page navigation buttons
                        updatePageButtons();
                    } else {
                        throw new Error(data.message || 'Failed to load document previews');
                    }
                })
                .catch(error => {
                    console.error('Error loading office document:', error);
                    if (officeLoading) officeLoading.style.display = 'none';
                    
                    // Show error message
                    container.innerHTML = `
                        <div class="unsupported-file">
                            <div class="unsupported-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h3 class="unsupported-title">Error Loading Document</h3>
                            <p class="unsupported-text">There was an error loading the document. Please try again later.</p>
                        </div>
                    `;
                });
            
            // Render a specific page
            function renderPage(pageNum) {
                if (!pageImages[pageNum - 1]) return;
                
                const img = new Image();
                img.crossOrigin = "anonymous";
                img.onload = function() {
                    // Set canvas size based on image
                    canvas.width = img.width;
                    canvas.height = img.height;
                    
                    // Clear canvas
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    
                    // Draw image
                    ctx.drawImage(img, 0, 0);
                    
                    // Add watermark
                    addWatermark(ctx, canvas.width, canvas.height);
                    
                    // Scroll to top of container
                    container.scrollTop = 0;
                };
                img.src = pageImages[pageNum - 1] + '&token=<?php echo $secure_token; ?>';
            }
            
            function addWatermark(ctx, width, height) {
                const text = '<?php echo htmlspecialchars($user_email); ?> - <?php echo date('Y-m-d H:i:s'); ?>';
                
                ctx.save();
                ctx.globalAlpha = 0.1;
                ctx.font = '20px Arial';
                ctx.fillStyle = '#000000';
                ctx.translate(width/2, height/2);
                ctx.rotate(-Math.PI/4);
                ctx.textAlign = 'center';
                ctx.fillText(text, 0, 0);
                ctx.restore();
            }
            
            // Update page navigation buttons
            function updatePageButtons() {
                if (prevPage) prevPage.disabled = currentPage <= 1;
                if (nextPage) nextPage.disabled = currentPage >= totalPages;
            }
            
            // Page navigation
            if (prevPage) {
                prevPage.addEventListener('click', function() {
                    if (currentPage <= 1) return;
                    currentPage--;
                    renderPage(currentPage);
                    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
                    updatePageButtons();
                });
            }
            
            if (nextPage) {
                nextPage.addEventListener('click', function() {
                    if (currentPage >= totalPages) return;
                    currentPage++;
                    renderPage(currentPage);
                    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
                    updatePageButtons();
                });
            }
            <?php endif; ?>
            
            <?php if ($viewer_type === 'text'): ?>
            // Secure Text Viewer Implementation
            const textArea = document.querySelector('.preview-text');
            
            // Apply security to the text area
            if (textArea) {
                applySecurityMeasures(textArea);
                
                // Disable copy/paste
                textArea.addEventListener('copy', function(e) {
                    e.preventDefault();
                    showNotification("Copying is disabled.");
                    logSecurityEventToDatabase('copy_attempt', 'User attempted to copy text');
                    return false;
                });
                
                textArea.addEventListener('cut', function(e) {
                    e.preventDefault();
                    showNotification("Cutting is disabled.");
                    logSecurityEventToDatabase('cut_attempt', 'User attempted to cut text');
                    return false;
                });
                
                textArea.addEventListener('paste', function(e) {
                    e.preventDefault();
                    showNotification("Pasting is disabled.");
                    logSecurityEventToDatabase('paste_attempt', 'User attempted to paste text');
                    return false;
                });
            }
            <?php endif; ?>
            
            // Toggle fullscreen functionality
            const toggleFullscreen = document.getElementById('toggleFullscreen');
            const previewContainer = document.querySelector('.preview-container');
            
            if (toggleFullscreen && previewContainer) {
                toggleFullscreen.addEventListener('click', function() {
                    if (!document.fullscreenElement) {
                        if (previewContainer.requestFullscreen) {
                            previewContainer.requestFullscreen();
                        } else if (previewContainer.mozRequestFullScreen) {
                            previewContainer.mozRequestFullScreen();
                        } else if (previewContainer.webkitRequestFullscreen) {
                            previewContainer.webkitRequestFullscreen();
                        } else if (previewContainer.msRequestFullscreen) {
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
        });
    </script>
</body>
</html>