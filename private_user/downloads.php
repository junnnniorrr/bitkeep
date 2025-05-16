<?php
require_once("include/connection.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['file_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['file_id']);

    // Fetch file to download from database
    $sql = "SELECT * FROM upload_files WHERE ID = $id";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Error fetching file: " . mysqli_error($conn));
    }

    $file = mysqli_fetch_assoc($result);
    if (!$file) {
        die("File not found.");
    }

    $filepath = '../uploads/' . $file['NAME'];

    if (file_exists($filepath)) {
        // Get user email from session
        $userEmail = isset($_SESSION['email_address']) ? $_SESSION['email_address'] : 'anonymous@example.com';

        // Log the download
        $accessType = 'download';
        $currentTime = date('Y-m-d H:i:s');

        $logQuery = "INSERT INTO file_access_logs (file_id, user_email, access_type, access_time)
                     VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $logQuery);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isss", $id, $userEmail, $accessType, $currentTime);
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_error($stmt)) {
                error_log("Error logging download: " . mysqli_stmt_error($stmt));
            }

            mysqli_stmt_close($stmt);
        } else {
            error_log("Error preparing log statement: " . mysqli_error($conn));
        }

        // Update download count
        $newCount = $file['DOWNLOAD'] + 1;
        $updateQuery = "UPDATE upload_files SET DOWNLOAD = $newCount WHERE ID = $id";
        mysqli_query($conn, $updateQuery);

        // Send headers and download file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));

        readfile($filepath);
        exit;
    } else {
        die("Error: File does not exist.");
    }
} else {
    die("Error: File ID not specified.");
}
?>
