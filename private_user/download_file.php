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

$file_path = $file_info['file_path'];
$file_name = $file_info['name'];
$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

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

// Log the file download
$current_time = date("Y-m-d H:i:s");
$log_stmt = $conn->prepare("INSERT INTO file_access_logs (file_id, user_email, access_type, access_time) VALUES (?, ?, 'download', ?)");
$log_stmt->bind_param("iss", $file_id, $user_email, $current_time);
$log_stmt->execute();
$log_stmt->close();

// Check if file exists
if (!file_exists($file_path)) {
    $_SESSION['error'] = "File not found on server.";
    header("location:user_dashboard.php");
    exit();
}

// Get appropriate MIME type
$mime_type = getMimeType($file_extension);

// Set headers to force download
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Clear output buffer
ob_clean();
flush();

// Read file and output to browser
readfile($file_path);
exit;
?>