<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION["email_address"])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

// Include database connection
require_once("../include/connection.php");

// Get user email from session
$user_id = $_SESSION["email_address"];
$stmt = $conn->prepare("SELECT email_address FROM login_user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_row = $result->fetch_assoc();
    $user_email = $user_row['email_address'];
} else {
    http_response_code(401);
    exit(json_encode(['error' => 'User not found']));
}
$stmt->close();

// Handle both JSON POST and fallback GET requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    if (!$data) {
        http_response_code(400);
        exit(json_encode(['error' => 'Invalid JSON data']));
    }
    
    $file_id = isset($data['file_id']) ? $data['file_id'] : null;
    $event_type = isset($data['event_type']) ? $data['event_type'] : null;
    $details = isset($data['details']) ? $data['details'] : null;
    
    // Use provided user_email if available, otherwise use session email
    $event_user_email = isset($data['user_email']) && !empty($data['user_email']) ? $data['user_email'] : $user_email;
} else {
    // Fallback GET method
    $file_id = isset($_GET['file_id']) ? $_GET['file_id'] : null;
    $event_type = isset($_GET['event']) ? $_GET['event'] : null;
    $details = isset($_GET['details']) ? $_GET['details'] : null;
    $event_user_email = isset($_GET['user_email']) && !empty($_GET['user_email']) ? $_GET['user_email'] : $user_email;
}

// Validate required fields
if (!$file_id || !$event_type) {
    http_response_code(400);
    exit(json_encode(['error' => 'Missing required fields']));
}

// Get IP address
$ip_address = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

// Get current time
$event_time = date("Y-m-d H:i:s");

// Insert into security_logs table
$log_stmt = $conn->prepare("INSERT INTO security_logs (file_id, user_email, event_type, details, ip_address, event_time) VALUES (?, ?, ?, ?, ?, ?)");
$log_stmt->bind_param("isssss", $file_id, $event_user_email, $event_type, $details, $ip_address, $event_time);

$success = $log_stmt->execute();
$log_stmt->close();

if ($success) {
    // Return success response
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => true, 'message' => 'Security event logged successfully']);
    } else {
        // For image fallback, just return a 1x1 transparent GIF
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    }
} else {
    http_response_code(500);
    exit(json_encode(['error' => 'Failed to log security event']));
}
?>
