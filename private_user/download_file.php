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

// Store referrer URL for redirecting back after download
if (!isset($_SESSION['download_referrer']) && isset($_SERVER['HTTP_REFERER'])) {
    $_SESSION['download_referrer'] = $_SERVER['HTTP_REFERER'];
}

// Get the return URL - either from referrer or default to dashboard
$return_url = isset($_SESSION['download_referrer']) ? $_SESSION['download_referrer'] : 'user_dashboard.php';

// Check if the watermark acknowledgment step is completed
if (!isset($_GET['acknowledged']) || $_GET['acknowledged'] != 'true') {
    // Display watermark/sensitive data warning
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sensitive Document Notice</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
                color: #333;
            }
            .container {
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
            }
            .watermark {
                position: relative;
                padding: 30px;
                margin-bottom: 30px;
                border: 2px dashed #cc0000;
            }
            .watermark::before {
                content: "CONFIDENTIAL";
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 80px;
                color: rgba(204, 0, 0, 0.2);
                z-index: 0;
                pointer-events: none;
            }
            .content {
                position: relative;
                z-index: 1;
            }
            h1 {
                color: #cc0000;
            }
            .btn {
                display: inline-block;
                background-color: #cc0000;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
                font-weight: bold;
            }
            p {
                line-height: 1.6;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="watermark">
                <div class="content">
                    <h1>SENSITIVE DOCUMENT NOTICE</h1>
                    <p><strong>File: <?php echo htmlspecialchars($file_name); ?></strong></p>
                    <p>You are about to download a sensitive document that is the property of Company A.</p>
                    <p>This document contains confidential information and should not be shared with unauthorized individuals.</p>
                    <p>By proceeding with this download, you acknowledge that:</p>
                    <ul style="text-align: left; display: inline-block;">
                        <li>You will handle this document according to Company A's data security policies</li>
                        <li>You will not distribute this document to unauthorized parties</li>
                        <li>You understand that misuse of this information may result in disciplinary action</li>
                        <li>This download will be logged and monitored</li>
                    </ul>
                </div>
            </div>
            
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?file_id=' . htmlspecialchars($file_id) . '&acknowledged=true&return=' . urlencode($return_url); ?>" class="btn">I Acknowledge - Download File</a>
            
            <p><a href="<?php echo htmlspecialchars($return_url); ?>" style="color: #666; margin-top: 20px; display: inline-block;">Cancel Download</a></p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Log the file download
$current_time = date("Y-m-d H:i:s");
$log_stmt = $conn->prepare("INSERT INTO file_access_logs (file_id, user_email, access_type, access_time, acknowledgment) VALUES (?, ?, 'download', ?, 'watermark_acknowledged')");
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

// Get return URL from GET parameter or session
$return_url = isset($_GET['return']) ? $_GET['return'] : (isset($_SESSION['download_referrer']) ? $_SESSION['download_referrer'] : 'user_dashboard.php');

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

// Clean up the session variable
if (isset($_SESSION['download_referrer'])) {
    unset($_SESSION['download_referrer']);
}

// Use JavaScript to redirect back to the previous page after download
?>
<script>
    // Start download
    document.addEventListener('DOMContentLoaded', function() {
        // Wait a moment to ensure download starts
        setTimeout(function() {
            // Redirect back to the referring page
            window.location.href = '<?php echo htmlspecialchars($return_url); ?>';
        }, 1500); // Wait 1.5 seconds before redirecting
    });
</script>
<?php
exit;
?>