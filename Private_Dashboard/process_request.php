<?php
require_once("../include/connection.php");

// Function to check and update expired requests
function checkExpiredRequests($conn) {
    $expire_query = "UPDATE folder_requests 
                     SET is_expired = TRUE, status = 'expired' 
                     WHERE status = 'approved' 
                     AND expiry_date < NOW() 
                     AND is_expired = FALSE";
    return $conn->query($expire_query);
}

// Function to extend expiry for a request
function extendRequestExpiry($conn, $request_id, $additional_hours = 48) {
    $extend_query = "UPDATE folder_requests 
                     SET expiry_date = DATE_ADD(COALESCE(expiry_date, NOW()), INTERVAL ? HOUR),
                         is_expired = FALSE 
                     WHERE id = ? AND status = 'approved'";
    $stmt = $conn->prepare($extend_query);
    $stmt->bind_param("ii", $additional_hours, $request_id);
    return $stmt->execute();
}

// Check for expired requests first
checkExpiredRequests($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $admin_email = $_POST['admin_email'];
    $assigned_folder_id = !empty($_POST['assigned_folder_id']) ? $_POST['assigned_folder_id'] : null;

    // Handle different actions
    switch ($action) {
        case 'approve':
            $status = 'approved';
            $current_time = date("Y-m-d H:i:s");
            $expiry_time = date("Y-m-d H:i:s", strtotime('+48 hours'));
            
            // Update with approval date and expiry date
            $stmt = $conn->prepare("UPDATE folder_requests 
                                   SET status = ?, admin_email = ?, assigned_folder_id = ?, 
                                       approval_date = ?, expiry_date = ?, is_expired = FALSE 
                                   WHERE id = ?");
            $stmt->bind_param("ssissi", $status, $admin_email, $assigned_folder_id, $current_time, $expiry_time, $request_id);
            
            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Request approved successfully. Access will expire in 48 hours on " . date("M j, Y g:i A", strtotime($expiry_time));
                header("Location: manage_requests.php?success=1");
            } else {
                $_SESSION['admin_error'] = "Error approving request: " . $stmt->error;
                header("Location: manage_requests.php?error=1");
            }
            break;
            
        case 'reject':
            $status = 'rejected';
            $stmt = $conn->prepare("UPDATE folder_requests SET status = ?, admin_email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $admin_email, $request_id);
            
            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Request rejected successfully.";
                header("Location: manage_requests.php?success=1");
            } else {
                $_SESSION['admin_error'] = "Error rejecting request: " . $stmt->error;
                header("Location: manage_requests.php?error=1");
            }
            break;
            
        case 'extend':
            $additional_hours = isset($_POST['additional_hours']) ? (int)$_POST['additional_hours'] : 48;
            
            if (extendRequestExpiry($conn, $request_id, $additional_hours)) {
                $_SESSION['admin_message'] = "Request expiry extended by {$additional_hours} hours successfully.";
                header("Location: manage_requests.php?success=1");
            } else {
                $_SESSION['admin_error'] = "Error extending request expiry.";
                header("Location: manage_requests.php?error=1");
            }
            break;
            
        case 'reactivate':
            // Reactivate an expired request
            $current_time = date("Y-m-d H:i:s");
            $new_expiry = date("Y-m-d H:i:s", strtotime('+48 hours'));
            
            $stmt = $conn->prepare("UPDATE folder_requests 
                                   SET status = 'approved', is_expired = FALSE, 
                                       expiry_date = ?, admin_email = ?
                                   WHERE id = ? AND status = 'expired'");
            $stmt->bind_param("ssi", $new_expiry, $admin_email, $request_id);
            
            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Request reactivated successfully. New expiry: " . date("M j, Y g:i A", strtotime($new_expiry));
                header("Location: manage_requests.php?success=1");
            } else {
                $_SESSION['admin_error'] = "Error reactivating request: " . $stmt->error;
                header("Location: manage_requests.php?error=1");
            }
            break;
            
        default:
            $_SESSION['admin_error'] = "Invalid action specified.";
            header("Location: manage_requests.php?error=1");
            break;
    }
} else {
    // If not a POST request, redirect back
    header("Location: manage_requests.php");
    exit();
}
?>