<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION["email_address"])) {
    header("location:../login.html");
    exit();
}

// Include database connection
require_once("include/connection.php");

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $user_email = isset($_POST['user_email']) ? trim($_POST['user_email']) : '';
    $requested_folder_name = isset($_POST['requested_folder_name']) ? trim($_POST['requested_folder_name']) : '';
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($user_email)) {
        $errors[] = "User email is required";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($requested_folder_name)) {
        $errors[] = "Folder name is required";
    } elseif (strlen($requested_folder_name) > 50) {
        $errors[] = "Folder name must be less than 50 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9\s_-]+$/', $requested_folder_name)) {
        $errors[] = "Folder name contains invalid characters";
    }
    
    if (empty($reason)) {
        $errors[] = "Reason for request is required";
    } elseif (strlen($reason) > 500) {
        $errors[] = "Reason must be less than 500 characters";
    }
    
    // Check if user exists in the database
    $user_check_query = "SELECT id FROM login_user WHERE email_address = ?";
    $user_stmt = $conn->prepare($user_check_query);
    $user_stmt->bind_param("s", $user_email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows === 0) {
        $errors[] = "User not found in the system";
    }
    
    // Check if the folder name already exists in the requests
    $folder_check_query = "SELECT id FROM folder_requests WHERE requested_folder_name = ? AND (status = 'pending' OR status = 'approved')";
    $folder_stmt = $conn->prepare($folder_check_query);
    $folder_stmt->bind_param("s", $requested_folder_name);
    $folder_stmt->execute();
    $folder_result = $folder_stmt->get_result();
    
    if ($folder_result->num_rows > 0) {
        $errors[] = "A folder with this name already exists or is pending approval";
    }
    
    // If no errors, insert the request into database
    if (empty($errors)) {
        $status = "pending"; // Default status for new requests
        $current_date = date("Y-m-d H:i:s");
        
        $insert_query = "INSERT INTO folder_requests (user_email, requested_folder_name, reason, status, request_date) 
                        VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("sssss", $user_email, $requested_folder_name, $reason, $status, $current_date);
        
        if ($insert_stmt->execute()) {
            // Success - Set success message in session and redirect
            $_SESSION['folder_request_success'] = "Your folder request has been submitted successfully and is pending approval.";
            header("location: user_dashboard.php");
            exit();
        } else {
            // Database error
            $_SESSION['folder_request_error'] = "Error submitting request: " . $conn->error;
            header("location: request_folder.php");
            exit();
        }
    } else {
        // Store errors in session and redirect back to form
        $_SESSION['folder_request_errors'] = $errors;
        header("location: request_folder.php");
        exit();
    }
} else {
    // If not a POST request, redirect to the form page
    header("location: request_folder.php");
    exit();
}
?>