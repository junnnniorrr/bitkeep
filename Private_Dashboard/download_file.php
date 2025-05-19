<?php
// Initialize session
session_start();

// Include database connection
require_once("include/connection.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.html');
    exit();
}

// Get admin info for logging
$admin_id = $_SESSION['admin_user'];
$query_admin = mysqli_query($conn, "SELECT admin_user FROM admin_login WHERE id = '$admin_id'") or die(mysqli_error($conn));
$row_admin = mysqli_fetch_array($query_admin);
$admin_email = $row_admin['admin_user'];

// Check if file_id is provided
if (!isset($_GET['file_id']) || empty($_GET['file_id'])) {
    echo '<script>alert("No file specified."); window.history.back();</script>';
    exit();
}

$file_id = $_GET['file_id'];

// Get file information
$query_file = "SELECT * FROM folder_files WHERE id = '$file_id'";
$result_file = mysqli_query($conn, $query_file);

if (!$result_file || mysqli_num_rows($result_file) == 0) {
    echo '<script>alert("File not found!"); window.history.back();</script>';
    exit();
}

$file_info = mysqli_fetch_assoc($result_file);
$file_path = $file_info['file_path'];
$file_name = $file_info['name'];
$folder_id = $file_info['folder_id'];
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
$ip_address = $_SERVER['REMOTE_ADDR'];

// Log in admin_file_logs
$log_query = "INSERT INTO admin_file_logs (admin_email, file_id, file_name, folder_id, action, timestamp, ip_address) 
              VALUES ('$admin_email', '$file_id', '$file_name', '$folder_id', 'download', '$current_time', '$ip_address')";
mysqli_query($conn, $log_query);

// Check if file exists
if (!file_exists($file_path)) {
    echo '<script>alert("File not found on server."); window.history.back();</script>';
    exit();
}

// Get appropriate MIME type
$mime_type = getMimeType($file_extension);

// Set up headers for download
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