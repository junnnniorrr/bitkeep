<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION["email_address"])) {
    http_response_code(401);
    exit('Unauthorized');
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
    // User not found, return error
    http_response_code(401);
    exit('User not found');
}
$stmt->close();

// Check if required parameters are provided
if (!isset($_POST['file_id']) || !isset($_POST['event_type'])) {
    http_response_code(400);
    exit('Missing required parameters');
}

$file_id = $_POST['file_id'];
$event_type = $_POST['event_type'];
$details = isset($_POST['details']) ? $_POST['details'] : '';

// Get IP address
$ip_address = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

// Get user agent
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';

// Current time
$current_time = date("Y-m-d H:i:s");

// Prepare and execute the query
$stmt = $conn->prepare("INSERT INTO security_logs (file_id, user_email, event_type, details, ip_address, user_agent, event_time) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    error_log("Error preparing security log statement: " . $conn->error);
    http_response_code(500);
    exit('Database error');
}

$stmt->bind_param("issssss", $file_id, $user_email, $event_type, $details, $ip_address, $user_agent, $current_time);
$result = $stmt->execute();

if (!$result) {
    error_log("Error executing security log statement: " . $stmt->error);
    http_response_code(500);
    exit('Failed to log security event');
}

$stmt->close();

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>