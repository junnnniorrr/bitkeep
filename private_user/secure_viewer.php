<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION["email_address"])) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

// Check if token is valid
if (!isset($_GET['token']) || !isset($_SESSION['file_view_token']) || $_GET['token'] !== $_SESSION['file_view_token']) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
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
    // User not found, return error
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}
$stmt->close();

// Check if file_id is provided
if (!isset($_GET['file_id']) || empty($_GET['file_id'])) {
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'No file specified']);
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
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$file_info = $result->fetch_assoc();
$stmt->close();

// Get file extension
$file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
$file_path = $file_info['file_path'];

// Log the file view
$current_time = date("Y-m-d H:i:s");
$log_stmt = $conn->prepare("INSERT INTO file_access_logs (file_id, user_email, access_type, access_time) VALUES (?, ?, 'preview', ?)");
$log_stmt->bind_param("iss", $file_id, $user_email, $current_time);
$log_stmt->execute();
$log_stmt->close();

// Check the requested type
$type = isset($_GET['type']) ? $_GET['type'] : '';

switch ($type) {
    case 'pdf':
        // For PDF files, serve the file directly with security headers
        if ($file_extension === 'pdf') {
            // Set headers to prevent caching
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($file_info['name']) . '"');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Output the file
            readfile($file_path);
        } else {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['success' => false, 'message' => 'Not a PDF file']);
        }
        break;
        
    case 'office':
        // For office documents, generate image previews
        if (in_array($file_extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
            // This would typically use a library like LibreOffice or a service like unoconv
            // to convert office documents to images. For this example, we'll simulate it.
            
            // In a real implementation, you would:
            // 1. Convert the document to PDF using LibreOffice, unoconv, or similar
            // 2. Convert the PDF to images using ImageMagick or similar
            // 3. Return the URLs to these images
            
            // Simulated response with dummy image URLs
            $pages = [];
            $page_count = rand(3, 10); // Simulate random page count
            
            for ($i = 1; $i <= $page_count; $i++) {
                $pages[] = "office_preview.php?file_id={$file_id}&page={$i}";
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'pages' => $pages,
                'total' => $page_count
            ]);
        } else {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['success' => false, 'message' => 'Not an office document']);
        }
        break;
        
    default:
        // For images and other files, serve with appropriate headers
        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $mime_type = 'image/' . ($file_extension === 'jpg' ? 'jpeg' : $file_extension);
            header("Content-Type: {$mime_type}");
            header('Content-Disposition: inline; filename="' . basename($file_info['name']) . '"');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Output the file
            readfile($file_path);
        } else {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['success' => false, 'message' => 'Unsupported file type']);
        }
        break;
}
?>