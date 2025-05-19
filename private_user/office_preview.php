<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION["email_address"])) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

// Check if token is valid
if (!isset($_GET['token']) || !isset($_SESSION['file_view_token']) || $_GET['token'] !== $_SESSION['file_view_token']) {
    header('HTTP/1.0 403 Forbidden');
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
    exit();
}
$stmt->close();

// Check if file_id is provided
if (!isset($_GET['file_id']) || empty($_GET['file_id'])) {
    header('HTTP/1.0 400 Bad Request');
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
    exit();
}

$file_info = $result->fetch_assoc();
$stmt->close();

// Get page number
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

// In a real implementation, you would:
// 1. Check if the page image already exists in a cache
// 2. If not, generate it using a document conversion tool
// 3. Add a watermark to the image
// 4. Serve the image

// For this example, we'll generate a placeholder image with text
$width = 800;
$height = 1100;
$image = imagecreatetruecolor($width, $height);

// Fill with white background
$white = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $white);

// Add some text
$textColor = imagecolorallocate($image, 0, 0, 0);
$font = 5; // Built-in font

// Draw a border
$borderColor = imagecolorallocate($image, 200, 200, 200);
imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);

// Add file name
$fileName = isset($file_info['name']) ? $file_info['name'] : 'Document';
imagestring($image, $font, 50, 50, "File: " . $fileName, $textColor);

// Add page number
imagestring($image, $font, 50, 80, "Page " . $page, $textColor);

// Add watermark
$watermarkText = isset($user_email) ? $user_email . ' - ' . date('Y-m-d H:i:s') : 'Confidential';
$watermarkColor = imagecolorallocate($image, 220, 220, 220);

// Rotate text for watermark
for ($i = 0; $i < $height; $i += 100) {
    for ($j = 0; $j < $width; $j += 300) {
        imagestringup($image, $font, $j, $i + 100, $watermarkText, $watermarkColor);
    }
}

// Output image
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
imagepng($image);
imagedestroy($image);
?>