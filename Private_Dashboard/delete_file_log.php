<?php
// Initialize session
session_start();

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['admin_user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'No log ID provided']);
    exit();
}

// Include database connection
require_once("../include/connection.php");

// Sanitize input
$log_id = mysqli_real_escape_string($conn, $_POST['id']);

// Delete the log entry
$query = "DELETE FROM file_access_logs WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $log_id);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    // Log the admin action
    $admin_email = $_SESSION['admin_user'];
    $action = "Deleted file access log with ID: " . $log_id;
    $timestamp = date("Y-m-d H:i:s");
    
    $log_query = "INSERT INTO admin_logs (admin_email, action, timestamp) VALUES (?, ?, ?)";
    $log_stmt = mysqli_prepare($conn, $log_query);
    mysqli_stmt_bind_param($log_stmt, "sss", $admin_email, $action, $timestamp);
    mysqli_stmt_execute($log_stmt);
    mysqli_stmt_close($log_stmt);
    
    echo json_encode(['success' => true, 'message' => 'Log entry deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting log entry: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>